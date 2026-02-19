"use client";

import { type ChangeEvent, type FormEvent, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { ROOM_CONFIG } from "@/lib/assessment/room-config";
import type { RoomType } from "@/lib/assessment/schema";

type SelectedFile = {
  localId: string;
  file: File;
  previewUrl: string;
  warnings: string[];
};

function createLocalId() {
  return typeof crypto !== "undefined" && "randomUUID" in crypto
    ? crypto.randomUUID()
    : `file-${Date.now()}-${Math.random()}`;
}

async function detectImageWarnings(file: File): Promise<string[]> {
  const warnings: string[] = [];

  if (file.size > 8 * 1024 * 1024) {
    warnings.push("Deze foto is groot. Gebruik bij voorkeur een scherpere foto met minder digitale zoom.");
  }

  if (typeof createImageBitmap === "undefined") {
    return warnings;
  }

  try {
    const bitmap = await createImageBitmap(file);

    if (bitmap.width < 900 || bitmap.height < 700) {
      warnings.push("Resolutie is vrij laag. Neem de foto iets verder weg en scherper.");
    }

    const canvas = document.createElement("canvas");
    canvas.width = Math.min(bitmap.width, 320);
    canvas.height = Math.min(bitmap.height, 240);
    const ctx = canvas.getContext("2d");

    if (!ctx) {
      bitmap.close();
      return warnings;
    }

    ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

    let brightness = 0;
    for (let i = 0; i < data.length; i += 4) {
      const luma = 0.2126 * data[i] + 0.7152 * data[i + 1] + 0.0722 * data[i + 2];
      brightness += luma;
    }
    brightness = brightness / (data.length / 4);

    if (brightness < 55) {
      warnings.push("Foto lijkt te donker. Zet extra verlichting aan en probeer opnieuw.");
    }

    let edgeStrength = 0;
    for (let y = 1; y < canvas.height - 1; y += 4) {
      for (let x = 1; x < canvas.width - 1; x += 4) {
        const idx = (y * canvas.width + x) * 4;
        const right = (y * canvas.width + (x + 1)) * 4;
        const down = ((y + 1) * canvas.width + x) * 4;

        const gx = Math.abs(data[idx] - data[right]);
        const gy = Math.abs(data[idx] - data[down]);
        edgeStrength += gx + gy;
      }
    }

    const samples = ((canvas.width / 4) * (canvas.height / 4)) || 1;
    const avgEdge = edgeStrength / samples;
    if (avgEdge < 18) {
      warnings.push("Foto kan onscherp zijn. Houd camera stabiel en focus opnieuw.");
    }

    bitmap.close();
  } catch {
    warnings.push("Kwaliteitscheck kon niet volledig worden uitgevoerd voor deze foto.");
  }

  return warnings;
}

export function AssessmentWizard({ roomType }: { roomType: RoomType }) {
  const router = useRouter();
  const config = ROOM_CONFIG[roomType];
  const [files, setFiles] = useState<SelectedFile[]>([]);
  const [consent, setConsent] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const canSubmit = useMemo(() => {
    return consent && files.length >= config.minPhotos && files.length <= config.maxPhotos && !isSubmitting;
  }, [consent, files.length, config.minPhotos, config.maxPhotos, isSubmitting]);

  async function onFileChange(event: ChangeEvent<HTMLInputElement>) {
    const selected = Array.from(event.target.files ?? []);
    event.target.value = "";

    if (selected.length === 0) {
      return;
    }

    const roomLeft = config.maxPhotos - files.length;
    const trimmed = selected.slice(0, Math.max(0, roomLeft));

    const nextItems: SelectedFile[] = [];
    for (const file of trimmed) {
      const warnings = await detectImageWarnings(file);
      nextItems.push({
        localId: createLocalId(),
        file,
        previewUrl: URL.createObjectURL(file),
        warnings
      });
    }

    setFiles((prev) => [...prev, ...nextItems].slice(0, config.maxPhotos));
    setError(null);
  }

  function onRemove(localId: string) {
    setFiles((prev) => {
      const target = prev.find((item) => item.localId === localId);
      if (target) {
        URL.revokeObjectURL(target.previewUrl);
      }
      return prev.filter((item) => item.localId !== localId);
    });
  }

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);

    if (!consent) {
      setError("Geef eerst toestemming voor verwerking van de foto's.");
      return;
    }

    if (files.length < config.minPhotos) {
      setError(`Upload minimaal ${config.minPhotos} foto's voor een betrouwbare analyse.`);
      return;
    }

    setIsSubmitting(true);

    try {
      const formData = new FormData();
      files.forEach((item) => formData.append("files", item.file));

      const uploadRes = await fetch("/api/assessment/upload", {
        method: "POST",
        body: formData
      });
      const uploadJson = await uploadRes.json();

      if (!uploadRes.ok) {
        throw new Error(uploadJson.error ?? "Upload mislukt.");
      }

      const analyzeRes = await fetch("/api/assessment/analyze", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          room_type: roomType,
          file_ids: (uploadJson.files as Array<{ file_id: string }>).map((f) => f.file_id)
        })
      });

      const analyzeJson = await analyzeRes.json();
      if (!analyzeRes.ok) {
        throw new Error(analyzeJson.error ?? "Analyse mislukt.");
      }

      router.push(`/assessment/result/${analyzeJson.assessment_id}`);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Er ging iets mis. Probeer opnieuw.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <form onSubmit={onSubmit} className="grid" style={{ gap: "1.2rem" }}>
      <section className="card">
        <h1>{config.title} uploaden</h1>
        <p className="muted">{config.subtitle}</p>
        <ol>
          {config.prompts.map((prompt, idx) => (
            <li key={prompt} style={{ marginBottom: "0.35rem" }}>
              {prompt} {files[idx] ? "✓" : ""}
            </li>
          ))}
        </ol>
      </section>

      <section className="card grid">
        <label htmlFor="photos">Foto's (JPG/PNG, max 10MB per foto, maximaal 5)</label>
        <input
          id="photos"
          className="input"
          type="file"
          accept="image/png,image/jpeg"
          multiple
          onChange={onFileChange}
          disabled={isSubmitting || files.length >= config.maxPhotos}
        />
        <p className="muted">{files.length} / {config.maxPhotos} foto's geselecteerd</p>

        <div className="grid grid-2" style={{ gap: "0.8rem" }}>
          {files.map((item) => (
            <article key={item.localId} className="card" style={{ padding: "0.75rem" }}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={item.previewUrl} alt={item.file.name} className="preview" />
              <p style={{ margin: "0.5rem 0 0" }}>{item.file.name}</p>
              {item.warnings.length > 0 ? (
                <ul style={{ margin: "0.5rem 0", paddingLeft: "1.1rem" }}>
                  {item.warnings.map((warning) => (
                    <li key={warning} className="muted">
                      {warning}
                    </li>
                  ))}
                </ul>
              ) : null}
              <button type="button" className="btn btn-secondary" onClick={() => onRemove(item.localId)}>
                Verwijder
              </button>
            </article>
          ))}
        </div>
      </section>

      <section className="card">
        <label style={{ display: "flex", gap: "0.6rem", alignItems: "flex-start" }}>
          <input type="checkbox" checked={consent} onChange={(e) => setConsent(e.target.checked)} />
          <span>
            Ik geef toestemming om deze foto's tijdelijk te verwerken voor een woonveiligheidsanalyse.
          </span>
        </label>
        <p className="muted" style={{ marginBottom: 0 }}>
          Beelden worden geoptimaliseerd en tijdelijk opgeslagen. Je kunt ze na afloop verwijderen.
        </p>
      </section>

      {error ? <p className="error">{error}</p> : null}

      <button className="btn" type="submit" disabled={!canSubmit}>
        {isSubmitting ? "Analyseren..." : "Analyse starten"}
      </button>
    </form>
  );
}
