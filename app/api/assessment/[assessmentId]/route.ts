import { NextResponse } from "next/server";
import { deleteAssessmentAndFiles, readAssessmentRecord } from "@/lib/assessment/storage";

export const runtime = "nodejs";

export async function GET(
  _request: Request,
  context: {
    params: Promise<{
      assessmentId: string;
    }>;
  }
) {
  const { assessmentId } = await context.params;
  const assessment = await readAssessmentRecord(assessmentId);

  if (!assessment) {
    return NextResponse.json({ error: "Assessment niet gevonden." }, { status: 404 });
  }

  return NextResponse.json(assessment);
}

export async function DELETE(
  _request: Request,
  context: {
    params: Promise<{
      assessmentId: string;
    }>;
  }
) {
  const { assessmentId } = await context.params;
  const deleted = await deleteAssessmentAndFiles(assessmentId);

  if (!deleted) {
    return NextResponse.json({ error: "Assessment niet gevonden." }, { status: 404 });
  }

  return NextResponse.json({ ok: true });
}
