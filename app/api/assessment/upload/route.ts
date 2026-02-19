import { NextResponse } from "next/server";
import { processAndStoreUploads, validateUploadConstraints } from "@/lib/assessment/upload";

export const runtime = "nodejs";

export async function POST(request: Request) {
  try {
    const formData = await request.formData();
    const values = formData.getAll("files");
    const files = values.filter((value): value is File => value instanceof File);

    const validation = validateUploadConstraints(files);

    if (!validation.ok) {
      return NextResponse.json({ error: validation.message }, { status: 400 });
    }

    const uploads = await processAndStoreUploads(files);

    return NextResponse.json({
      files: uploads.map((upload) => ({
        file_id: upload.id,
        mime_type: upload.mime_type,
        width: upload.width,
        height: upload.height,
        size_bytes: upload.size_bytes
      }))
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Onbekende uploadfout.";
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
