import fs from "node:fs/promises";
import path from "node:path";
import { randomUUID } from "node:crypto";
import sharp from "sharp";
import { ensureStorageDirs, saveUploadRecord } from "@/lib/assessment/storage";
import type { UploadRecord } from "@/lib/assessment/schema";

const ACCEPTED_TYPES = new Set(["image/jpeg", "image/png"]);
const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;
const MAX_IMAGES = 5;

export type UploadValidationResult =
  | { ok: true }
  | {
      ok: false;
      message: string;
    };

export function validateUploadConstraints(files: File[]): UploadValidationResult {
  if (files.length < 1) {
    return { ok: false, message: "Upload minimaal 1 foto." };
  }

  if (files.length > MAX_IMAGES) {
    return { ok: false, message: "Upload maximaal 5 foto's." };
  }

  for (const file of files) {
    if (!ACCEPTED_TYPES.has(file.type)) {
      return { ok: false, message: `Bestandstype niet toegestaan: ${file.name}. Gebruik JPG of PNG.` };
    }

    if (file.size > MAX_FILE_SIZE_BYTES) {
      return { ok: false, message: `Bestand te groot: ${file.name}. Maximaal 10MB per foto.` };
    }
  }

  return { ok: true };
}

export async function processAndStoreUploads(files: File[]): Promise<UploadRecord[]> {
  const dirs = await ensureStorageDirs();

  return Promise.all(
    files.map(async (file) => {
      const input = Buffer.from(await file.arrayBuffer());
      const transformed = sharp(input, { failOn: "none" })
        .rotate()
        .resize({ width: 1600, withoutEnlargement: true });

      const targetMimeType = file.type === "image/png" ? "image/png" : "image/jpeg";
      const fileId = randomUUID();

      const outputBuffer =
        targetMimeType === "image/png"
          ? await transformed.png({ compressionLevel: 9, quality: 85 }).toBuffer()
          : await transformed.jpeg({ quality: 82, mozjpeg: true }).toBuffer();

      const metadata = await sharp(outputBuffer).metadata();
      const extension = targetMimeType === "image/png" ? "png" : "jpg";
      const outputPath = path.join(dirs.uploads, `${fileId}.${extension}`);

      // Do not include EXIF metadata: sharp omits metadata unless explicitly set.
      await fs.writeFile(outputPath, outputBuffer);

      const uploadRecord: UploadRecord = {
        id: fileId,
        mime_type: targetMimeType,
        path: outputPath,
        size_bytes: outputBuffer.byteLength,
        width: metadata.width ?? 0,
        height: metadata.height ?? 0,
        created_at: new Date().toISOString()
      };

      await saveUploadRecord(uploadRecord);

      return uploadRecord;
    })
  );
}
