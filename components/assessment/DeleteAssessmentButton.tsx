"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";

export function DeleteAssessmentButton({ assessmentId }: { assessmentId: string }) {
  const router = useRouter();
  const [isDeleting, setIsDeleting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function onDelete() {
    const confirmed = window.confirm("Weet je zeker dat je deze analyse en foto's wilt verwijderen?");
    if (!confirmed) {
      return;
    }

    setIsDeleting(true);
    setError(null);

    try {
      const response = await fetch(`/api/assessment/${assessmentId}`, {
        method: "DELETE"
      });

      if (!response.ok) {
        const payload = await response.json();
        throw new Error(payload.error ?? "Verwijderen mislukt.");
      }

      router.push("/assessment");
      router.refresh();
    } catch (err) {
      setError(err instanceof Error ? err.message : "Onbekende fout bij verwijderen.");
    } finally {
      setIsDeleting(false);
    }
  }

  return (
    <div className="no-print">
      <button className="btn btn-secondary" type="button" onClick={onDelete} disabled={isDeleting}>
        {isDeleting ? "Verwijderen..." : "Verwijder analyse en foto's"}
      </button>
      {error ? <p className="error">{error}</p> : null}
    </div>
  );
}
