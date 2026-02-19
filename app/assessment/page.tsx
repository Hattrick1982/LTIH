import Link from "next/link";
import { ROOM_CONFIG } from "@/lib/assessment/room-config";

export default function AssessmentLandingPage() {
  return (
    <div className="grid" style={{ gap: "1.4rem" }}>
      <section className="card">
        <h1>Foto-assessment Woonveiligheid</h1>
        <p className="muted">
          Kies een ruimte, upload foto's via de begeleide flow en ontvang direct een risico-analyse met verbeterplan.
        </p>
      </section>

      <section className="grid grid-2">
        <article className="card">
          <h2>{ROOM_CONFIG.bathroom.title}</h2>
          <p className="muted">{ROOM_CONFIG.bathroom.subtitle}</p>
          <p>Benodigde foto's: {ROOM_CONFIG.bathroom.minPhotos} - {ROOM_CONFIG.bathroom.maxPhotos}</p>
          <Link className="btn" href="/assessment/new?room=bathroom">
            Start Badkamer
          </Link>
        </article>

        <article className="card">
          <h2>{ROOM_CONFIG.stairs_hall.title}</h2>
          <p className="muted">{ROOM_CONFIG.stairs_hall.subtitle}</p>
          <p>Benodigde foto's: {ROOM_CONFIG.stairs_hall.minPhotos} - {ROOM_CONFIG.stairs_hall.maxPhotos}</p>
          <Link className="btn" href="/assessment/new?room=stairs_hall">
            Start Trap/hal
          </Link>
        </article>
      </section>

      <section className="card">
        <strong>Belangrijk:</strong>
        <p className="muted" style={{ marginBottom: 0 }}>
          Deze analyse is informatief en vervangt geen medisch advies of diagnose.
        </p>
      </section>
    </div>
  );
}
