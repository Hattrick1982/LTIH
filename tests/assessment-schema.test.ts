import { describe, expect, it } from "vitest";
import { AssessmentResultSchema, RoomTypeSchema } from "@/lib/assessment/schema";

describe("AssessmentResultSchema", () => {
  it("accepteert een geldige payload", () => {
    const payload = {
      room_type: "bathroom",
      overall_risk_score_0_100: 68,
      hazards: [
        {
          category: "slip_hazard",
          severity_1_5: 4,
          confidence_0_1: 0.88,
          what_we_see: "Gladde tegelvloer bij douche-instap.",
          why_it_matters: "Natte tegels verhogen kans op uitglijden.",
          suggested_actions: [
            {
              action: "Plaats antislipmat in de douche.",
              effort: "laag",
              cost_band: "laag"
            }
          ],
          needs_human_followup: true
        }
      ],
      missing_info_questions: ["Is er een steunbeugel naast het toilet?"],
      disclaimer: "Deze analyse is informatief en geen medisch advies."
    };

    const result = AssessmentResultSchema.parse(payload);
    expect(result.overall_risk_score_0_100).toBe(68);
  });

  it("weigert ongeldige severity", () => {
    const payload = {
      room_type: "bathroom",
      overall_risk_score_0_100: 68,
      hazards: [
        {
          category: "slip_hazard",
          severity_1_5: 8,
          confidence_0_1: 0.88,
          what_we_see: "x",
          why_it_matters: "y",
          suggested_actions: [
            {
              action: "z",
              effort: "laag",
              cost_band: "laag"
            }
          ],
          needs_human_followup: true
        }
      ],
      missing_info_questions: [],
      disclaimer: "Deze analyse is informatief en geen medisch advies."
    };

    expect(() => AssessmentResultSchema.parse(payload)).toThrow();
  });

  it("accepteert nieuwe ruimtes", () => {
    expect(RoomTypeSchema.parse("living_room")).toBe("living_room");
    expect(RoomTypeSchema.parse("bedroom")).toBe("bedroom");
    expect(RoomTypeSchema.parse("kitchen")).toBe("kitchen");
  });
});
