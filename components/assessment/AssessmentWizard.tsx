"use client";

import { type ChangeEvent, type FormEvent, useEffect, useMemo, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { AssessmentStepper } from "@/components/assessment/AssessmentStepper";
import { ROOM_CONFIG, type RoomChecklistItem } from "@/lib/assessment/room-config";
import type { RoomType } from "@/lib/assessment/schema";

type ItemPhotoState = {
  file: File | null;
  previewUrl: string | null;
  error: string | null;
};

type PickerMode = "camera" | "gallery";

const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;
const ALLOWED_MIME_TYPES = new Set(["image/jpeg", "image/png"]);

function createInitialItemState(items: RoomChecklistItem[]) {
  return items.reduce<Record<string, ItemPhotoState>>((acc, item) => {
    acc[item.id] = { file: null, previewUrl: null, error: null };
    return acc;
  }, {});
}

function revokePreviewUrl(url: string | null) {
  if (url) {
    URL.revokeObjectURL(url);
  }
}

function validatePhotoFile(file: File): string | null {
  if (!ALLOWED_MIME_TYPES.has(file.type)) {
    return "Ongeldig bestandstype. Gebruik alleen JPG of PNG.";
  }

  if (file.size > MAX_FILE_SIZE_BYTES) {
    return "Bestand te groot. Maximaal 10MB per foto.";
  }

  return null;
}

export function AssessmentWizard({ roomType }: { roomType: RoomType }) {
  const router = useRouter();
  const config = ROOM_CONFIG[roomType];

  const promptItems = useMemo(() => config.items, [config.items]);
  const requiredItems = useMemo(() => promptItems.filter((item) => item.required), [promptItems]);
  const optionalItems = useMemo(() => promptItems.filter((item) => !item.required), [promptItems]);

  const [itemPhotos, setItemPhotos] = useState<Record<string, ItemPhotoState>>(() =>
    createInitialItemState(promptItems)
  );
  const itemPhotosRef = useRef(itemPhotos);

  const [consent, setConsent] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const cameraInputRefs = useRef<Record<string, HTMLInputElement | null>>({});
  const galleryInputRefs = useRef<Record<string, HTMLInputElement | null>>({});

  useEffect(() => {
    itemPhotosRef.current = itemPhotos;
  }, [itemPhotos]);

  useEffect(() => {
    setItemPhotos((previous) => {
      Object.values(previous).forEach((photo) => revokePreviewUrl(photo.previewUrl));
      return createInitialItemState(promptItems);
    });
  }, [promptItems]);

  useEffect(() => {
    return () => {
      Object.values(itemPhotosRef.current).forEach((photo) => revokePreviewUrl(photo.previewUrl));
    };
  }, []);

  const selectedCount = useMemo(() => {
    return promptItems.reduce((count, item) => {
      return itemPhotos[item.id]?.file ? count + 1 : count;
    }, 0);
  }, [itemPhotos, promptItems]);

  const progressHint =
    selectedCount < config.minPhotos
      ? `Minimaal ${config.minPhotos} foto's nodig om door te gaan.`
      : `Minimaal gehaald. Voeg tot ${config.maxPhotos} foto's toe voor beter advies.`;

  const canSubmit =
    consent && selectedCount >= config.minPhotos && selectedCount <= config.maxPhotos && !isSubmitting;

  function openPicker(itemId: string, mode: PickerMode) {
    const refMap = mode === "camera" ? cameraInputRefs.current : galleryInputRefs.current;
    refMap[itemId]?.click();
  }

  function removeItemPhoto(itemId: string) {
    setItemPhotos((previous) => {
      const current = previous[itemId];
      if (!current) {
        return previous;
      }

      revokePreviewUrl(current.previewUrl);
      return {
        ...previous,
        [itemId]: { file: null, previewUrl: null, error: null }
      };
    });
  }

  function onItemFileChange(itemId: string, event: ChangeEvent<HTMLInputElement>) {
    const file = event.target.files?.[0] ?? null;
    event.target.value = "";

    if (!file) {
      return;
    }

    setItemPhotos((previous) => {
      const current = previous[itemId] ?? { file: null, previewUrl: null, error: null };
      const currentCount = Object.values(previous).filter((photo) => photo.file).length;

      if (!current.file && currentCount >= config.maxPhotos) {
        return {
          ...previous,
          [itemId]: {
            ...current,
            error: `Je kunt maximaal ${config.maxPhotos} foto's toevoegen.`
          }
        };
      }

      const validationError = validatePhotoFile(file);
      if (validationError) {
        return {
          ...previous,
          [itemId]: {
            ...current,
            error: validationError
          }
        };
      }

      const previewUrl = URL.createObjectURL(file);
      revokePreviewUrl(current.previewUrl);

      return {
        ...previous,
        [itemId]: {
          file,
          previewUrl,
          error: null
        }
      };
    });

    setError(null);
  }

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);

    if (!consent) {
      setError("Geef eerst toestemming voor verwerking van de foto's.");
      return;
    }

    const selectedFiles = promptItems
      .map((item) => itemPhotos[item.id]?.file)
      .filter((file): file is File => file instanceof File);

    if (selectedFiles.length < config.minPhotos) {
      setError(
        `Upload minimaal ${config.minPhotos} foto's om te starten. Voor de beste analyse adviseren we ${config.maxPhotos} foto's.`
      );
      return;
    }

    const filesByItem = promptItems
      .map((item) => {
        const file = itemPhotos[item.id]?.file;

        if (!(file instanceof File)) {
          return null;
        }

        return {
          itemId: item.id,
          label: item.label,
          fileName: file.name,
          type: file.type,
          size: file.size
        };
      })
      .filter((entry): entry is NonNullable<typeof entry> => entry !== null);

    if (typeof window !== "undefined") {
      console.log("Mock submit foto-assessment", {
        roomKey: config.roomKey,
        filesByItem
      });
    }

    setIsSubmitting(true);

    try {
      const formData = new FormData();
      selectedFiles.forEach((file) => formData.append("files", file));

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

  const sectionGroups = [
    {
      key: "required",
      title: `Minimaal nodig (${config.minPhotos} foto's)`,
      items: requiredItems
    },
    {
      key: "optional",
      title: "Aanbevolen (voor beter advies)",
      items: optionalItems
    }
  ];

  return (
    <form onSubmit={onSubmit} className="grid" style={{ gap: "1.2rem" }}>
      <AssessmentStepper currentStep="upload" />
      <section className="card">
        <h1>{config.title}</h1>
        <p className="muted">{config.subtitle}</p>

        <div className="upload-progress" aria-live="polite">
          <strong>{selectedCount}/{config.maxPhotos} foto's toegevoegd</strong>
          <span>{progressHint}</span>
        </div>

        {sectionGroups.map((section) => (
          <div key={section.key} className="prompt-section">
            <p className="prompt-section-title">{section.title}</p>

            <ul className="prompt-list">
              {section.items.map((item) => {
                const state = itemPhotos[item.id] ?? { file: null, previewUrl: null, error: null };
                const hasPhoto = Boolean(state.file);

                return (
                  <li key={item.id}>
                    <div className="prompt-item-row">
                      <div className="prompt-item-copy">
                        <span className="prompt-item-label">{item.label}</span>
                        <span className="prompt-item-tip">{item.tip}</span>
                      </div>

                      <div className="prompt-item-controls">
                        {hasPhoto && state.previewUrl ? (
                          // eslint-disable-next-line @next/next/no-img-element
                          <img
                            src={state.previewUrl}
                            alt={`Preview foto voor: ${item.label}`}
                            className="prompt-thumb"
                          />
                        ) : null}

                        {!hasPhoto ? (
                          <>
                            <button
                              type="button"
                              className="btn btn-secondary file-picker-btn"
                              onClick={() => openPicker(item.id, "camera")}
                              aria-label={`Foto toevoegen voor: ${item.label}`}
                              disabled={isSubmitting}
                            >
                              Maak foto
                            </button>
                            <button
                              type="button"
                              className="prompt-link-btn"
                              onClick={() => openPicker(item.id, "gallery")}
                              aria-label={`Foto kiezen uit galerij voor: ${item.label}`}
                              disabled={isSubmitting}
                            >
                              Kies uit galerij
                            </button>
                          </>
                        ) : (
                          <>
                            <button
                              type="button"
                              className="btn btn-secondary prompt-action-btn"
                              onClick={() => openPicker(item.id, "camera")}
                              aria-label={`Foto vervangen voor: ${item.label}`}
                              disabled={isSubmitting}
                            >
                              Vervang
                            </button>
                            <button
                              type="button"
                              className="prompt-link-btn"
                              onClick={() => openPicker(item.id, "gallery")}
                              aria-label={`Foto kiezen uit galerij voor: ${item.label}`}
                              disabled={isSubmitting}
                            >
                              Kies uit galerij
                            </button>
                            <button
                              type="button"
                              className="btn btn-secondary prompt-action-btn"
                              onClick={() => removeItemPhoto(item.id)}
                              aria-label={`Foto verwijderen voor: ${item.label}`}
                              disabled={isSubmitting}
                            >
                              Verwijder
                            </button>
                          </>
                        )}

                        <input
                          ref={(el) => {
                            cameraInputRefs.current[item.id] = el;
                          }}
                          className="file-picker-input"
                          type="file"
                          accept="image/*"
                          capture="environment"
                          onChange={(event) => onItemFileChange(item.id, event)}
                          aria-label={`Camera foto toevoegen voor: ${item.label}`}
                          disabled={isSubmitting}
                        />

                        <input
                          ref={(el) => {
                            galleryInputRefs.current[item.id] = el;
                          }}
                          className="file-picker-input"
                          type="file"
                          accept="image/png,image/jpeg"
                          onChange={(event) => onItemFileChange(item.id, event)}
                          aria-label={`Galerij foto toevoegen voor: ${item.label}`}
                          disabled={isSubmitting}
                        />
                      </div>
                    </div>

                    {state.error ? <p className="prompt-item-error">{state.error}</p> : null}
                  </li>
                );
              })}
            </ul>
          </div>
        ))}

        <p className="muted" style={{ marginTop: "0.85rem", marginBottom: 0 }}>
          Je foto's worden alleen gebruikt voor dit advies.
        </p>
      </section>

      <section className="card">
        <label style={{ display: "flex", gap: "0.6rem", alignItems: "flex-start" }}>
          <input type="checkbox" checked={consent} onChange={(e) => setConsent(e.target.checked)} />
          <span>Ik geef toestemming om deze foto's tijdelijk te verwerken voor een woonveiligheidsanalyse.</span>
        </label>
        <p className="muted" style={{ marginBottom: 0 }}>
          Beelden worden geoptimaliseerd en tijdelijk opgeslagen. Je kunt ze na afloop verwijderen.
        </p>
      </section>

      {error ? <p className="error">{error}</p> : null}

      <button className="btn" type="submit" disabled={!canSubmit}>
        {isSubmitting ? "Analyse gestart, dit kan een paar minuten duren" : "Analyse starten"}
      </button>
    </form>
  );
}
