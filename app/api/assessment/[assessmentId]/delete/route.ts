import { NextResponse } from "next/server";
import { deleteAssessmentAndFiles } from "@/lib/assessment/storage";

export const runtime = "nodejs";

export async function POST(
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
