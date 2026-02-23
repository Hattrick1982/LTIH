import type { RoomType } from "@/lib/assessment/schema";

export type RoomChecklistItem = {
  id: string;
  label: string;
  tip: string;
  required: boolean;
};

export type RoomConfig = {
  roomKey: RoomType;
  title: string;
  subtitle: string;
  minPhotos: number;
  maxPhotos: number;
  items: RoomChecklistItem[];
};

export const ROOM_CONFIG: Record<RoomType, RoomConfig> = {
  bathroom: {
    roomKey: "bathroom",
    title: "Badkamercheck: veilig en toegankelijk",
    subtitle:
      "We beoordelen waar de badkamer veiliger en makkelijker kan, zoals bij uitglijden, opstaan en instappen. Je ontvangt een helder advies met concrete verbeterpunten en passende opties voor aanpassingen.",
    minPhotos: 2,
    maxPhotos: 5,
    items: [
      {
        id: "bathroom-overview-door",
        label: "Overzicht vanaf de deur",
        tip: "Laat vloer en belangrijkste loopruimte duidelijk zien.",
        required: true
      },
      {
        id: "bathroom-shower-bath-entry",
        label: "Douche of bad (instap)",
        tip: "Zorg dat instaprand en vloer zichtbaar zijn.",
        required: true
      },
      {
        id: "bathroom-route",
        label: "Looproute (richting douche, wastafel, toilet)",
        tip: "Fotografeer vanuit stahoogte in de looprichting.",
        required: false
      },
      {
        id: "bathroom-obstacles",
        label: "Obstakels in beeld (drempels, matten, kabels)",
        tip: "Neem details van struikelpunten van dichtbij op.",
        required: false
      },
      {
        id: "bathroom-toilet-zone",
        label: "Toiletzone (optioneel, maar aanbevolen)",
        tip: "Laat ruimte naast het toilet en eventuele steun zien.",
        required: false
      }
    ]
  },
  stairs_hall: {
    roomKey: "stairs_hall",
    title: "Trap en hal: voorkom struikelen",
    subtitle:
      "Upload 2 tot 5 foto's van trap en hal. We letten op drempels, losse vloerkleden, verlichting en mogelijkheden voor extra houvast. Je ontvangt direct tips en een concreet verbeterplan.",
    minPhotos: 2,
    maxPhotos: 5,
    items: [
      {
        id: "stairs-overview-entry",
        label: "Overzicht vanaf de entree van de hal of trap",
        tip: "Laat de vloer, trapaanzet en doorgang zien.",
        required: true
      },
      {
        id: "stairs-route-turns",
        label: "Looproute en draaipunten",
        tip: "Leg bochten en smalle stukken duidelijk vast.",
        required: true
      },
      {
        id: "stairs-obstacles",
        label: "Obstakels in beeld (kleden, kabels, drempels)",
        tip: "Fotografeer risico's op of naast de looproute.",
        required: false
      },
      {
        id: "stairs-handrail-edges",
        label: "Leuning of trapranden",
        tip: "Laat leuninghoogte en trede-randen goed zien.",
        required: false
      },
      {
        id: "stairs-lighting",
        label: "Verlichting in hal en op trap",
        tip: "Toon lampen en schakelaars in de looproute.",
        required: false
      }
    ]
  },
  living_room: {
    roomKey: "living_room",
    title: "Woonkamercheck: veilig en comfortabel",
    subtitle:
      "We beoordelen waar de woonkamer veiliger en makkelijker kan. Je krijgt een helder advies met concrete verbeterpunten.",
    minPhotos: 2,
    maxPhotos: 5,
    items: [
      {
        id: "living-overview-entry",
        label: "Overzicht vanaf de ingang (toon vloer en zitgedeelte)",
        tip: "Neem een brede foto zodat vloer en meubels samen zichtbaar zijn.",
        required: true
      },
      {
        id: "living-route",
        label: "Looproute door de kamer (richting deur of gang)",
        tip: "Fotografeer vanuit de looprichting op stahoogte.",
        required: true
      },
      {
        id: "living-obstacles",
        label: "Obstakels en struikelpunten (losse kleedjes, drempels, kabels)",
        tip: "Maak een detailfoto van elk duidelijk struikelpunt.",
        required: false
      },
      {
        id: "living-lighting-switches",
        label: "Verlichting en schakelaars (zicht bij avond, looproute)",
        tip: "Toon lichtpunten en schakelaarlocaties langs de route.",
        required: false
      },
      {
        id: "living-seating-standup",
        label: "Zitplek opstaan (bank/stoel, zithoogte en armleuningen)",
        tip: "Laat de belangrijkste zitplek van de zijkant zien.",
        required: false
      }
    ]
  },
  bedroom: {
    roomKey: "bedroom",
    title: "Slaapkamercheck: veilig opstaan en lopen",
    subtitle:
      "We beoordelen waar de slaapkamer veiliger en makkelijker kan, vooral bij opstaan en 's nachts lopen. Je krijgt een concreet verbeterplan.",
    minPhotos: 2,
    maxPhotos: 5,
    items: [
      {
        id: "bedroom-overview-door",
        label: "Overzicht vanaf de deur (bed en vloer zichtbaar)",
        tip: "Zorg dat bed, doorgang en vloer volledig in beeld staan.",
        required: true
      },
      {
        id: "bedroom-route-bed-door",
        label: "Looproute bed naar deur (nachtelijke route)",
        tip: "Neem de route vanaf bedhoogte en vanaf stahoogte op.",
        required: true
      },
      {
        id: "bedroom-bed-height-entry",
        label: "Bedhoogte en instap (zijkant bed, ruimte ernaast)",
        tip: "Laat de instapzijde van het bed en vrije ruimte zien.",
        required: false
      },
      {
        id: "bedroom-obstacles",
        label: "Obstakels (kleedjes, snoeren, spullen op de vloer)",
        tip: "Focus op losse onderdelen rond bed en looproute.",
        required: false
      },
      {
        id: "bedroom-night-light",
        label: "Nachtverlichting (lamp, schakelaar, nachtlampje)",
        tip: "Toon hoe de route in het donker wordt verlicht.",
        required: false
      }
    ]
  },
  kitchen: {
    roomKey: "kitchen",
    title: "Keukencheck: veilig en goed bereikbaar",
    subtitle:
      "We beoordelen waar de keuken veiliger en makkelijker kan tijdens lopen, pakken en koken. Je ontvangt praktische adviezen en verbeterpunten.",
    minPhotos: 2,
    maxPhotos: 5,
    items: [
      {
        id: "kitchen-overview",
        label: "Overzicht van de keuken (werkblad en loopruimte)",
        tip: "Laat werkblad, vloer en doorgangen in een brede foto zien.",
        required: true
      },
      {
        id: "kitchen-route-counter-stove",
        label: "Loopruimte bij aanrecht en kookplaat (bewegingsruimte)",
        tip: "Fotografeer de route waar je het meest beweegt.",
        required: true
      },
      {
        id: "kitchen-storage-reach",
        label: "Opbergruimtes en bereikbaarheid (bovenkasten, lage kasten, veelgebruikte spullen)",
        tip: "Toon spullen die vaak gepakt worden en hoe hoog ze staan.",
        required: false
      },
      {
        id: "kitchen-floor-thresholds",
        label: "Vloer en drempels (gladheid, matten, hoogteverschillen)",
        tip: "Leg gladde zones en hoogteverschillen duidelijk vast.",
        required: false
      },
      {
        id: "kitchen-lighting-outlets",
        label: "Verlichting en stopcontacten (werklicht, snoeren, apparaten)",
        tip: "Laat kabels, apparaten en verlichting rond het werkblad zien.",
        required: false
      }
    ]
  }
};

export function isRoomType(value: string): value is RoomType {
  return Object.hasOwn(ROOM_CONFIG, value);
}
