"use client";

import { useId, useState } from "react";

export type AccordionIssue = {
  id: string;
  title: string;
  severity: number;
  confidence: number;
  whatWeSee: string;
  whyRisky: string;
  recommendedActions: string[];
  flagHumanFollowUp: boolean;
};

function severityClass(severity: number) {
  if (severity <= 2) {
    return "severity-low";
  }

  if (severity === 3) {
    return "severity-medium";
  }

  return "severity-high";
}

export function IssuesAccordion({ issues }: { issues: AccordionIssue[] }) {
  const idPrefix = useId();
  const [openIds, setOpenIds] = useState<Set<string>>(new Set());

  function toggleIssue(issueId: string) {
    setOpenIds((previous) => {
      const next = new Set(previous);
      if (next.has(issueId)) {
        next.delete(issueId);
      } else {
        next.add(issueId);
      }
      return next;
    });
  }

  if (issues.length === 0) {
    return <p>Er zijn geen duidelijke risico's gedetecteerd op basis van de foto's.</p>;
  }

  return (
    <div className="issue-accordion-list">
      {issues.map((issue, index) => {
        const isOpen = openIds.has(issue.id);
        const buttonId = `${idPrefix}-issue-button-${index}`;
        const panelId = `${idPrefix}-issue-panel-${index}`;

        return (
          <article key={issue.id} className="issue-accordion-card">
            <h3 className="issue-accordion-heading">
              <button
                id={buttonId}
                type="button"
                className="issue-accordion-trigger"
                aria-expanded={isOpen}
                aria-controls={panelId}
                onClick={() => toggleIssue(issue.id)}
              >
                <span className="issue-accordion-title">{issue.title}</span>

                <span className="issue-accordion-meta">
                  <span className={`issue-badge-compact ${severityClass(issue.severity)}`}>
                    Ernst {issue.severity}/5
                  </span>
                  <span className="issue-badge-compact confidence">Zekerheid {issue.confidence}%</span>
                  <span className={`issue-chevron ${isOpen ? "open" : ""}`} aria-hidden="true">
                    ▾
                  </span>
                </span>
              </button>
            </h3>

            <div
              id={panelId}
              role="region"
              aria-labelledby={buttonId}
              className={`issue-body-region ${isOpen ? "open" : ""}`}
            >
              <div className="issue-body-inner">
                <div className="issue-body-content">
                  <p>
                    <strong>Wat zien we</strong>
                    <br />
                    {issue.whatWeSee}
                  </p>

                  <p>
                    <strong>Waarom risicant</strong>
                    <br />
                    {issue.whyRisky}
                  </p>

                  <p style={{ marginBottom: "0.35rem" }}>
                    <strong>Aanbevolen acties</strong>
                  </p>
                  <ul>
                    {issue.recommendedActions.map((action) => (
                      <li key={`${issue.id}-${action}`}>{action}</li>
                    ))}
                  </ul>

                  {issue.flagHumanFollowUp ? (
                    <p className="issue-followup">Dit punt vraagt mogelijk menselijke opvolging.</p>
                  ) : null}
                </div>
              </div>
            </div>
          </article>
        );
      })}
    </div>
  );
}
