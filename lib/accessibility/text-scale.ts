export const TEXT_SCALE_STORAGE_KEY = "lthih_text_scale";

export const TEXT_SCALE_CLASS_NORMAL = "text-scale-normal";
export const TEXT_SCALE_CLASS_LARGE = "text-scale-large";

export type TextScale = "normal" | "large";

export function normalizeTextScale(value: string | null | undefined): TextScale {
  return value === "large" ? "large" : "normal";
}

export function getTextScaleClass(scale: TextScale): string {
  return scale === "large" ? TEXT_SCALE_CLASS_LARGE : TEXT_SCALE_CLASS_NORMAL;
}

export function applyTextScaleClass(root: { classList: { add: (...tokens: string[]) => void; remove: (...tokens: string[]) => void } }, scale: TextScale) {
  root.classList.remove(TEXT_SCALE_CLASS_NORMAL, TEXT_SCALE_CLASS_LARGE);
  root.classList.add(getTextScaleClass(scale));
}

export function getTextScaleInitScript() {
  return `
(function () {
  try {
    var key = "${TEXT_SCALE_STORAGE_KEY}";
    var value = window.localStorage.getItem(key);
    var scale = value === "large" ? "large" : "normal";
    var root = document.documentElement;
    root.classList.remove("${TEXT_SCALE_CLASS_NORMAL}", "${TEXT_SCALE_CLASS_LARGE}");
    root.classList.add(scale === "large" ? "${TEXT_SCALE_CLASS_LARGE}" : "${TEXT_SCALE_CLASS_NORMAL}");
  } catch (error) {
    document.documentElement.classList.remove("${TEXT_SCALE_CLASS_LARGE}");
    document.documentElement.classList.add("${TEXT_SCALE_CLASS_NORMAL}");
  }
})();
`.trim();
}
