import type { RoomType } from "@/lib/assessment/schema";

export type RoomNavItem = {
  roomKey: RoomType;
  label: string;
  hint: string;
  href: string;
};

export const ROOM_NAV_ITEMS: RoomNavItem[] = [
  {
    roomKey: "bathroom",
    label: "Badkamer",
    hint: "Check op glijgevaar, instap en extra houvast.",
    href: "/assessment/new?room=bathroom"
  },
  {
    roomKey: "stairs_hall",
    label: "Trap en hal",
    hint: "Voorkom struikelen bij drempels, kleedjes en kabels.",
    href: "/assessment/new?room=stairs_hall"
  },
  {
    roomKey: "living_room",
    label: "Woonkamer",
    hint: "Maak looproutes vrij en verbeter verlichting.",
    href: "/woonkamer"
  },
  {
    roomKey: "bedroom",
    label: "Slaapkamer",
    hint: "Veilige route naar bed en toilet, ook 's nachts.",
    href: "/slaapkamer"
  },
  {
    roomKey: "kitchen",
    label: "Keuken",
    hint: "Grip, overzicht en minder valrisico bij draaien en reiken.",
    href: "/keuken"
  }
];

const ROOM_PATH_MAP: Record<string, RoomType> = {
  "/woonkamer": "living_room",
  "/slaapkamer": "bedroom",
  "/keuken": "kitchen"
};

export function getActiveRoomKey(pathname: string, searchParams: URLSearchParams): RoomType | null {
  if (pathname === "/assessment/new") {
    const room = searchParams.get("room");

    if (
      room === "bathroom" ||
      room === "stairs_hall" ||
      room === "living_room" ||
      room === "bedroom" ||
      room === "kitchen"
    ) {
      return room;
    }

    return null;
  }

  return ROOM_PATH_MAP[pathname] ?? null;
}
