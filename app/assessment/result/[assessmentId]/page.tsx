import Link from "next/link";
import { notFound } from "next/navigation";
import { DeleteAssessmentButton } from "@/components/assessment/DeleteAssessmentButton";
import { categoryLabel, riskLabel } from "@/lib/assessment/presentation";
import { readAssessmentRecord } from "@/lib/assessment/storage";

export const runtime = "nodejs";
export const dynamic = "force-dynamic";

function buildActionPlan(
  hazards: Array<{
    severity_1_5: number;
    suggested_actions: Array<{ action: string; effort: "laag" | "middel" | "hoog" }>;
  }>
) {
  const today = new Set<string>();
  const week = new Set<string>();
  const months = new Set<string>();

  for (const hazard of hazards) {
    for (const item of hazard.suggested_actions) {
      if (hazard.severity_1_5 >= 4 || item.effort === "laag") {
        today.add(item.action);
      } else if (item.effort === "middel") {
        week.add(item.action);
      } else {
        months.add(item.action);
      }
    }
  }

  return {
    today: Array.from(today).slice(0, 8),
    week: Array.from(week).slice(0, 8),
    months: Array.from(months).slice(0, 8)
  };
}

export default async function AssessmentResultPage({
  params
}: {
  params: Promise<{ assessmentId: string }>;
}) {
  const { assessmentId } = await params;
  const assessment = await readAssessmentRecord(assessmentId);

  if (!assessment) {
    notFound();
  }

  const { result } = assessment;
  const risk = riskLabel(result.overall_risk_score_0_100);
  const topIssues = [...result.hazards]
    .sort((a, b) => b.severity_1_5 * b.confidence_0_1 - a.severity_1_5 * a.confidence_0_1)
    .slice(0, 5);
  const actionPlan = buildActionPlan(result.hazards);

  return (
    <div className="grid" style={{ gap: "1rem" }}>
      <section className="card">
        <h1>Resultaat woonveiligheidsanalyse</h1>
        <p className="muted">Assessment ID: {assessment.assessment_id}</p>
        <div style={{ display: "flex", alignItems: "center", gap: "0.8rem", flexWrap: "wrap" }}>
          <strong style={{ fontSize: "2rem" }}>{result.overall_risk_score_0_100}/100</strong>
          <span className={`badge ${risk.className}`}>{risk.label}</span>
        </div>
      </section>

      <section className="card">
        <h2>Top issues</h2>
        {topIssues.length === 0 ? (
          <p>Er zijn geen duidelijke risico's gedetecteerd op basis van de foto's.</p>
        ) : (
          <div className="grid" style={{ gap: "0.8rem" }}>
            {topIssues.map((hazard, idx) => (
              <article key={`${hazard.category}-${idx}`} className="card">
                <p style={{ marginTop: 0 }}>
                  <strong>{categoryLabel(hazard.category)}</strong> | Severity {hazard.severity_1_5}/5 | Confidence{" "}
                  {(hazard.confidence_0_1 * 100).toFixed(0)}%
                </p>
                <p>
                  <strong>Wat zien we:</strong> {hazard.what_we_see}
                </p>
                <p>
                  <strong>Waarom riskant:</strong> {hazard.why_it_matters}
                </p>
                <p>
                  <strong>Aanbevolen acties:</strong>
                </p>
                <ul>
                  {hazard.suggested_actions.map((action) => (
                    <li key={`${hazard.category}-${action.action}`}>
                      {action.action} ({action.effort}, kosten {action.cost_band})
                    </li>
                  ))}
                </ul>
                {hazard.needs_human_followup ? (
                  <p className="muted" style={{ marginBottom: 0 }}>
                    Deze observatie vraagt mogelijk menselijke opvolging.
                  </p>
                ) : null}
              </article>
            ))}
          </div>
        )}
      </section>

      <section className="card">
        <h2>Actieplan</h2>
        <h3>Vandaag</h3>
        <ul>
          {actionPlan.today.length > 0 ? actionPlan.today.map((item) => <li key={`t-${item}`}>{item}</li>) : <li>Geen directe acties.</li>}
        </ul>
        <h3>Deze week</h3>
        <ul>
          {actionPlan.week.length > 0 ? actionPlan.week.map((item) => <li key={`w-${item}`}>{item}</li>) : <li>Geen aanvullende acties.</li>}
        </ul>
        <h3>Binnen 1-3 maanden</h3>
        <ul>
          {actionPlan.months.length > 0 ? actionPlan.months.map((item) => <li key={`m-${item}`}>{item}</li>) : <li>Geen langetermijnacties.</li>}
        </ul>
      </section>

      {result.missing_info_questions.length > 0 ? (
        <section className="card">
          <h2>Vragen ter aanvulling</h2>
          <ul>
            {result.missing_info_questions.map((question) => (
              <li key={question}>{question}</li>
            ))}
          </ul>
        </section>
      ) : null}

      <section className="card">
        <p style={{ marginTop: 0 }}>
          <strong>Disclaimer:</strong> {result.disclaimer}
        </p>
        <p className="muted" style={{ marginBottom: 0 }}>
          Deze output is informatief en vervangt geen medisch advies, diagnose of acute hulpverlening.
        </p>
      </section>

      <section className="card no-print" style={{ display: "flex", gap: "0.6rem", flexWrap: "wrap" }}>
        <Link href={`/assessment/result/${assessmentId}/print`} className="btn btn-secondary">
          Print checklist
        </Link>
        <Link href="/contact" className="btn">
          Plan adviesgesprek
        </Link>
        <DeleteAssessmentButton assessmentId={assessmentId} />
      </section>
    </div>
  );
}
