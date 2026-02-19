import { describe, expect, it } from "vitest";
import { POST as analyzePost } from "@/app/api/assessment/analyze/route";

describe("POST /api/assessment/analyze", () => {
  it("geeft 400 bij ongeldige payload", async () => {
    const request = new Request("http://localhost/api/assessment/analyze", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        room_type: "invalid_room",
        file_ids: []
      })
    });

    const response = await analyzePost(request);
    expect(response.status).toBe(400);
  });
});
