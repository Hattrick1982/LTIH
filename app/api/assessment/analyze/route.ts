import { randomUUID } from "node:crypto";
import { NextResponse } from "next/server";
import { runAssessmentAnalysis } from "@/lib/assessment/analyze";
import { AnalyzeRequestSchema, type AssessmentRecord } from "@/lib/assessment/schema";
import { readUploadRecord, saveAssessmentRecord } from "@/lib/assessment/storage";

export const runtime = "nodejs";

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const parsed = AnalyzeRequestSchema.safeParse(body);

    if (!parsed.success) {
      return NextResponse.json(
        {
          error: "Ongeldige input voor analyse.",
          details: parsed.error.flatten()
        },
        { status: 400 }
      );
    }

    const uploadRecords = await Promise.all(parsed.data.file_ids.map((fileId) => readUploadRecord(fileId)));

    if (uploadRecords.some((record) => record === null)) {
      return NextResponse.json({ error: "Een of meer geuploade foto's zijn niet gevonden." }, { status: 404 });
    }

    const safeUploads = uploadRecords.filter((record): record is NonNullable<typeof record> => record !== null);

    const result = await runAssessmentAnalysis({
      roomType: parsed.data.room_type,
      uploads: safeUploads
    });

    const assessmentId = randomUUID();
    const record: AssessmentRecord = {
      assessment_id: assessmentId,
      created_at: new Date().toISOString(),
      file_ids: parsed.data.file_ids,
      result
    };

    await saveAssessmentRecord(record);

    return NextResponse.json({ assessment_id: assessmentId, ...result });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Onbekende analysefout.";
    return NextResponse.json({ error: message }, { status: 500 });
  }
}
