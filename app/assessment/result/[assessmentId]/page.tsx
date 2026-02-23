import Link from "next/link";
import { notFound } from "next/navigation";
import { AssessmentStepper } from "@/components/assessment/AssessmentStepper";
import { DeleteAssessmentButton } from "@/components/assessment/DeleteAssessmentButton";
import { IssuesAccordion, type AccordionIssue } from "@/components/assessment/IssuesAccordion";
import { categoryLabel, riskLabel } from "@/lib/assessment/presentation";
import { ASSESSMENT_DISCLAIMER_PARAGRAPHS } from "@/lib/assessment/disclaimer";
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
  const riskLabelLower = `${risk.label.charAt(0).toLowerCase()}${risk.label.slice(1)}`;
  const scoreMeterClass =
    risk.className === "label-low"
      ? "score-meter-fill-low"
      : risk.className === "label-medium"
        ? "score-meter-fill-medium"
        : "score-meter-fill-high";
  const topIssues = [...result.hazards]
    .sort((a, b) => b.severity_1_5 * b.confidence_0_1 - a.severity_1_5 * a.confidence_0_1)
    .slice(0, 5);
  const accordionIssues: AccordionIssue[] = topIssues.map((hazard, idx) => ({
    id: `${hazard.category}-${idx}`,
    title: categoryLabel(hazard.category),
    severity: hazard.severity_1_5,
    confidence: Math.round(hazard.confidence_0_1 * 100),
    whatWeSee: hazard.what_we_see,
    whyRisky: hazard.why_it_matters,
    recommendedActions: hazard.suggested_actions.map(
      (action) => `${action.action} (${action.effort}, kosten ${action.cost_band})`
    ),
    flagHumanFollowUp: hazard.needs_human_followup
  }));
  const actionPlan = buildActionPlan(result.hazards);

  return (
    <div className="grid" style={{ gap: "1rem" }}>
      <AssessmentStepper currentStep="result" />
      <section className="card">
        <h1>Jouw woonveiligheidsresultaat</h1>
        <p className="score-intro">
          Je score is {result.overall_risk_score_0_100}/100: {riskLabelLower}. Hieronder zie je de belangrijkste
          aandachtspunten en de eerste stappen om je woning veiliger te maken.
        </p>
        <div
          className="score-meter"
          role="img"
          aria-label={`Risicoscore ${result.overall_risk_score_0_100} van 100: ${riskLabelLower}`}
        >
          <div
            className={`score-meter-fill ${scoreMeterClass}`}
            style={{ width: `${Math.max(0, Math.min(100, result.overall_risk_score_0_100))}%` }}
          />
        </div>
      </section>

      <section className="card">
        <h2>Belangrijkste aandachtspunten</h2>
        <IssuesAccordion issues={accordionIssues} />
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
        <section className="card questions-card">
          <h2>Vragen ter aanvulling</h2>
          <ul>
            {result.missing_info_questions.map((question) => (
              <li key={question}>{question}</li>
            ))}
          </ul>
          <div className="questions-card-cta">
            <p className="questions-card-cta-text">
              Samen lopen we deze vragen door en krijg je een persoonlijk advies voor jouw situatie.
            </p>
            <Link href="/contact" className="btn btn-advice-cta">
              Plan een adviesgesprek
            </Link>
          </div>
        </section>
      ) : null}

      <section className="card disclaimer-card">
        <p style={{ marginTop: 0 }}>
          <strong>Disclaimer:</strong> {ASSESSMENT_DISCLAIMER_PARAGRAPHS[0]}
        </p>
        <p>{ASSESSMENT_DISCLAIMER_PARAGRAPHS[1]}</p>
        <p style={{ marginBottom: 0 }}>
          {ASSESSMENT_DISCLAIMER_PARAGRAPHS[2]}
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
