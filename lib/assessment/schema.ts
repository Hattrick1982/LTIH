import { z } from "zod";

export const RoomTypeSchema = z.enum(["bathroom", "stairs_hall"]);

export const ActionSchema = z.object({
  action: z.string().min(1).max(240),
  effort: z.enum(["laag", "middel", "hoog"]),
  cost_band: z.enum(["laag", "middel", "hoog"])
});

export const HazardSchema = z.object({
  category: z.enum([
    "tripping_hazard",
    "slip_hazard",
    "support_hazard",
    "lighting_hazard",
    "accessibility_hazard",
    "other"
  ]),
  severity_1_5: z.number().int().min(1).max(5),
  confidence_0_1: z.number().min(0).max(1),
  what_we_see: z.string().min(1).max(260),
  why_it_matters: z.string().min(1).max(320),
  suggested_actions: z.array(ActionSchema).min(1).max(5),
  needs_human_followup: z.boolean()
});

export const AssessmentResultSchema = z.object({
  room_type: RoomTypeSchema,
  overall_risk_score_0_100: z.number().int().min(0).max(100),
  hazards: z.array(HazardSchema).min(0).max(15),
  missing_info_questions: z.array(z.string().min(1).max(220)).min(0).max(5),
  disclaimer: z.string().min(1).max(500)
});

export const AnalyzeRequestSchema = z.object({
  room_type: RoomTypeSchema,
  file_ids: z.array(z.string().uuid()).min(1).max(5)
});

export type RoomType = z.infer<typeof RoomTypeSchema>;
export type AssessmentResult = z.infer<typeof AssessmentResultSchema>;
export type AnalyzeRequest = z.infer<typeof AnalyzeRequestSchema>;

export type AssessmentRecord = {
  assessment_id: string;
  created_at: string;
  file_ids: string[];
  result: AssessmentResult;
};

export type UploadRecord = {
  id: string;
  mime_type: "image/jpeg" | "image/png";
  path: string;
  size_bytes: number;
  width: number;
  height: number;
  created_at: string;
};
