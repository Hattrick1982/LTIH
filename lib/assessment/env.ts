import os from "node:os";
import path from "node:path";

const DEFAULT_MODEL = "gpt-5.2";
const DEFAULT_STORAGE = path.join(os.tmpdir(), "ltih-assessment");

export function getAssessmentEnv() {
  return {
    OPENAI_API_KEY: process.env.OPENAI_API_KEY,
    OPENAI_MODEL: process.env.OPENAI_MODEL ?? DEFAULT_MODEL,
    TEMP_STORAGE_PATH: process.env.TEMP_STORAGE_PATH ?? DEFAULT_STORAGE
  };
}
