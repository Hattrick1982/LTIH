import { describe, expect, it } from "vitest";
import {
  TEXT_SCALE_CLASS_LARGE,
  TEXT_SCALE_CLASS_NORMAL,
  TEXT_SCALE_STORAGE_KEY,
  applyTextScaleClass,
  getTextScaleClass,
  getTextScaleInitScript,
  normalizeTextScale
} from "@/lib/accessibility/text-scale";

function createMockRoot(initialClasses: string[] = []) {
  const classes = new Set(initialClasses);

  return {
    classes,
    root: {
      classList: {
        add: (...tokens: string[]) => {
          for (const token of tokens) {
            classes.add(token);
          }
        },
        remove: (...tokens: string[]) => {
          for (const token of tokens) {
            classes.delete(token);
          }
        }
      }
    }
  };
}

describe("text scale helpers", () => {
  it("normaliseert opslagwaarde naar ondersteunde schaal", () => {
    expect(normalizeTextScale("large")).toBe("large");
    expect(normalizeTextScale("normal")).toBe("normal");
    expect(normalizeTextScale("unknown")).toBe("normal");
    expect(normalizeTextScale(null)).toBe("normal");
  });

  it("geeft juiste class per schaal", () => {
    expect(getTextScaleClass("normal")).toBe(TEXT_SCALE_CLASS_NORMAL);
    expect(getTextScaleClass("large")).toBe(TEXT_SCALE_CLASS_LARGE);
  });

  it("past html classes consistent toe", () => {
    const { root, classes } = createMockRoot([TEXT_SCALE_CLASS_NORMAL]);

    applyTextScaleClass(root, "large");
    expect(classes.has(TEXT_SCALE_CLASS_LARGE)).toBe(true);
    expect(classes.has(TEXT_SCALE_CLASS_NORMAL)).toBe(false);

    applyTextScaleClass(root, "normal");
    expect(classes.has(TEXT_SCALE_CLASS_NORMAL)).toBe(true);
    expect(classes.has(TEXT_SCALE_CLASS_LARGE)).toBe(false);
  });

  it("genereert init script met storage key en classes", () => {
    const script = getTextScaleInitScript();

    expect(script).toContain(TEXT_SCALE_STORAGE_KEY);
    expect(script).toContain(TEXT_SCALE_CLASS_NORMAL);
    expect(script).toContain(TEXT_SCALE_CLASS_LARGE);
  });
});
