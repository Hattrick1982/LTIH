"use client";

export function PrintNowButton() {
  return (
    <button className="btn" type="button" onClick={() => window.print()}>
      Print nu
    </button>
  );
}
