import fs from "node:fs/promises";
import path from "node:path";
import { getAssessmentEnv } from "@/lib/assessment/env";
import type { AssessmentRecord, UploadRecord } from "@/lib/assessment/schema";

function getPaths() {
  const base = getAssessmentEnv().TEMP_STORAGE_PATH;
  return {
    base,
    uploads: path.join(base, "uploads"),
    assessments: path.join(base, "assessments")
  };
}

export async function ensureStorageDirs() {
  const dirs = getPaths();
  await fs.mkdir(dirs.uploads, { recursive: true });
  await fs.mkdir(dirs.assessments, { recursive: true });
  return dirs;
}

export async function saveUploadRecord(record: UploadRecord) {
  const dirs = await ensureStorageDirs();
  const metadataPath = path.join(dirs.uploads, `${record.id}.json`);
  await fs.writeFile(metadataPath, JSON.stringify(record, null, 2), "utf-8");
}

export async function readUploadRecord(fileId: string): Promise<UploadRecord | null> {
  const dirs = await ensureStorageDirs();
  const metadataPath = path.join(dirs.uploads, `${fileId}.json`);

  try {
    const raw = await fs.readFile(metadataPath, "utf-8");
    return JSON.parse(raw) as UploadRecord;
  } catch {
    return null;
  }
}

export async function saveAssessmentRecord(record: AssessmentRecord) {
  const dirs = await ensureStorageDirs();
  const resultPath = path.join(dirs.assessments, `${record.assessment_id}.json`);
  await fs.writeFile(resultPath, JSON.stringify(record, null, 2), "utf-8");
}

export async function readAssessmentRecord(assessmentId: string): Promise<AssessmentRecord | null> {
  const dirs = await ensureStorageDirs();
  const resultPath = path.join(dirs.assessments, `${assessmentId}.json`);

  try {
    const raw = await fs.readFile(resultPath, "utf-8");
    return JSON.parse(raw) as AssessmentRecord;
  } catch {
    return null;
  }
}

export async function deleteAssessmentRecord(assessmentId: string): Promise<boolean> {
  const dirs = await ensureStorageDirs();
  const resultPath = path.join(dirs.assessments, `${assessmentId}.json`);

  try {
    await fs.unlink(resultPath);
    return true;
  } catch {
    return false;
  }
}

export async function deleteUploadRecordAndImage(fileId: string): Promise<void> {
  const upload = await readUploadRecord(fileId);
  const dirs = await ensureStorageDirs();
  const metadataPath = path.join(dirs.uploads, `${fileId}.json`);

  if (upload?.path) {
    await fs.rm(upload.path, { force: true });
  }

  await fs.rm(metadataPath, { force: true });
}

export async function deleteAssessmentAndFiles(assessmentId: string): Promise<boolean> {
  const record = await readAssessmentRecord(assessmentId);

  if (!record) {
    return false;
  }

  for (const fileId of record.file_ids) {
    await deleteUploadRecordAndImage(fileId);
  }

  await deleteAssessmentRecord(assessmentId);
  return true;
}
