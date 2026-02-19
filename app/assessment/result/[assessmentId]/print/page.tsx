import Link from "next/link";
import { notFound } from "next/navigation";
import { PrintNowButton } from "@/components/assessment/PrintNowButton";
import { categoryLabel, riskLabel } from "@/lib/assessment/presentation";
import { readAssessmentRecord } from "@/lib/assessment/storage";

export const runtime = "nodejs";
export const dynamic = "force-dynamic";

export default async function PrintableChecklistPage({
  params
}: {
  params: Promise<{ assessmentId: string }>;
}) {
  const { assessmentId } = await params;
  const assessment = await readAssessmentRecord(assessmentId);

  if (!assessment) {
    notFound();
  }

  const risk = riskLabel(assessment.result.overall_risk_score_0_100);

  return (
    <div className="grid" style={{ gap: "1rem" }}>
      <section className="card">
        <h1>Checklist woonveiligheid</h1>
        <p className="muted">Assessment ID: {assessmentId}</p>
        <p>
          Risicoscore: <strong>{assessment.result.overall_risk_score_0_100}/100</strong>{" "}
          <span className={`badge ${risk.className}`}>{risk.label}</span>
        </p>
      </section>

      <section className="card">
        <h2>Issues en acties</h2>
        <ol>
          {assessment.result.hazards.map((hazard, idx) => (
            <li key={`${hazard.category}-${idx}`} style={{ marginBottom: "0.7rem" }}>
              <strong>{categoryLabel(hazard.category)}</strong> - {hazard.what_we_see}
              <ul>
                {hazard.suggested_actions.map((action) => (
                  <li key={`${action.action}-${idx}`}>{action.action}</li>
                ))}
              </ul>
            </li>
          ))}
        </ol>
      </section>

      <section className="card">
        <p>
          <strong>Disclaimer:</strong> {assessment.result.disclaimer}
        </p>
      </section>

      <section className="no-print" style={{ display: "flex", gap: "0.6rem" }}>
        <PrintNowButton />
        <Link href={`/assessment/result/${assessmentId}`} className="btn btn-secondary">
          Terug naar resultaat
        </Link>
      </section>
    </div>
  );
}
