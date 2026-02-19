import { describe, expect, it } from "vitest";
import { validateUploadConstraints } from "@/lib/assessment/upload";

describe("validateUploadConstraints", () => {
  it("weigert niet-ondersteund type", () => {
    const file = { name: "test.webp", type: "image/webp", size: 1234 } as File;
    const result = validateUploadConstraints([file]);
    expect(result.ok).toBe(false);
  });
});
