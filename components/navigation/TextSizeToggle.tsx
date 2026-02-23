"use client";

import { useEffect, useState } from "react";
import {
  TEXT_SCALE_STORAGE_KEY,
  type TextScale,
  applyTextScaleClass,
  normalizeTextScale
} from "@/lib/accessibility/text-scale";

type TextSizeToggleProps = {
  label: string;
  className?: string;
};

export function TextSizeToggle({ label, className = "" }: TextSizeToggleProps) {
  const [scale, setScale] = useState<TextScale>("normal");

  useEffect(() => {
    const stored = normalizeTextScale(window.localStorage.getItem(TEXT_SCALE_STORAGE_KEY));
    setScale(stored);
    applyTextScaleClass(document.documentElement, stored);
  }, []);

  const updateScale = (nextScale: TextScale) => {
    setScale(nextScale);
    applyTextScaleClass(document.documentElement, nextScale);

    try {
      window.localStorage.setItem(TEXT_SCALE_STORAGE_KEY, nextScale);
    } catch {
      // No-op when localStorage is unavailable.
    }
  };

  return (
    <div className={`text-size-control ${className}`.trim()}>
      <span className="text-size-control-label">{label}</span>

      <div className="text-size-control-group" role="group" aria-label={`${label} aanpassen`}>
        <button
          type="button"
          className={`text-size-control-btn ${scale === "normal" ? "active" : ""}`}
          onClick={() => updateScale("normal")}
          aria-label="Tekstgrootte normaal"
          aria-pressed={scale === "normal"}
        >
          A
        </button>

        <button
          type="button"
          className={`text-size-control-btn ${scale === "large" ? "active" : ""}`}
          onClick={() => updateScale("large")}
          aria-label="Tekstgrootte groter"
          aria-pressed={scale === "large"}
        >
          A+
        </button>
      </div>
    </div>
  );
}
