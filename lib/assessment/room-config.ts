import type { RoomType } from "@/lib/assessment/schema";

export type RoomConfig = {
  title: string;
  subtitle: string;
  minPhotos: number;
  maxPhotos: number;
  prompts: string[];
};

export const ROOM_CONFIG: Record<RoomType, RoomConfig> = {
  bathroom: {
    title: "Badkamer",
    subtitle: "Controleer glijgevaar, houvast en toegankelijkheid.",
    minPhotos: 4,
    maxPhotos: 5,
    prompts: [
      "Maak 1 overzichtsfoto vanaf de deur.",
      "Maak 1 foto van de looproute.",
      "Maak 1 detailfoto van obstakels (drempels, matten, kabels).",
      "Maak 1 foto van douche- of badinstap.",
      "Maak 1 foto van de toiletzone (optioneel maar aanbevolen)."
    ]
  },
  stairs_hall: {
    title: "Trap/hal",
    subtitle: "Controleer struikelgevaar, verlichting en steunpunten.",
    minPhotos: 3,
    maxPhotos: 5,
    prompts: [
      "Maak 1 overzichtsfoto vanaf de entree van de hal of trap.",
      "Maak 1 foto van de looproute en draaipunten.",
      "Maak 1 detailfoto van obstakels (kleden, kabels, drempels).",
      "Maak 1 extra foto van leuning of trapranden (aanbevolen)."
    ]
  }
};

export function isRoomType(value: string): value is RoomType {
  return value === "bathroom" || value === "stairs_hall";
}
