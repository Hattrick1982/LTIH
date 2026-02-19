import fs from "node:fs/promises";
import OpenAI from "openai";
import { zodTextFormat } from "openai/helpers/zod";
import { getAssessmentEnv } from "@/lib/assessment/env";
import { AssessmentResultSchema, type AssessmentResult, type RoomType, type UploadRecord } from "@/lib/assessment/schema";

const SYSTEM_PROMPT = [
  "Je bent een expert in woonveiligheid voor ouderen.",
  "Focus op valrisico en concrete, praktische verbeteringen.",
  "Geef geen medische diagnoses of medische claims.",
  "Als iets niet duidelijk zichtbaar is, voeg een vraag toe in missing_info_questions.",
  "Output moet uitsluitend geldige JSON zijn volgens het schema. Nooit extra tekst buiten JSON."
].join(" ");

function roomLabel(roomType: RoomType) {
  return roomType === "bathroom" ? "badkamer" : "trap/hal";
}

function buildUserInstruction(roomType: RoomType) {
  return [
    `Analyseer deze foto's van een ${roomLabel(roomType)} in huis.`,
    "Doel: valpreventie en seniorvriendelijke aanpassingen.",
    "Gebruik duidelijke Nederlandse formuleringen.",
    "Als confidence laag is of severity hoog, zet needs_human_followup op true.",
    "Belangrijk: retourneer uitsluitend JSON."
  ].join(" ");
}

export async function runAssessmentAnalysis(params: {
  roomType: RoomType;
  uploads: UploadRecord[];
}): Promise<AssessmentResult> {
  const env = getAssessmentEnv();

  if (!env.OPENAI_API_KEY) {
    throw new Error("OPENAI_API_KEY ontbreekt.");
  }

  const client = new OpenAI({ apiKey: env.OPENAI_API_KEY });

  const imageContent = await Promise.all(
    params.uploads.map(async (upload) => {
      const buffer = await fs.readFile(upload.path);
      const base64 = buffer.toString("base64");

      return {
        type: "input_image" as const,
        image_url: `data:${upload.mime_type};base64,${base64}`
      };
    })
  );

  const response = await client.responses.parse({
    model: env.OPENAI_MODEL,
    input: [
      {
        role: "system",
        content: [{ type: "input_text", text: SYSTEM_PROMPT }]
      },
      {
        role: "user",
        content: [{ type: "input_text", text: buildUserInstruction(params.roomType) }, ...imageContent]
      }
    ],
    text: {
      format: zodTextFormat(AssessmentResultSchema, "assessment_result")
    }
  });

  const parsed = response.output_parsed;

  if (!parsed) {
    throw new Error("Lege AI-response ontvangen.");
  }

  return AssessmentResultSchema.parse(parsed);
}
