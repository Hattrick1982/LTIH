import Link from "next/link";
import { AssessmentStepper } from "@/components/assessment/AssessmentStepper";

export default function AssessmentLandingPage() {
  return (
    <div className="grid" style={{ gap: "1.4rem" }}>
      <AssessmentStepper currentStep="choose" />

      <section className="hero assessment-hero">
        <p className="kicker">Woonveiligheidsscan</p>
        <h1>Start je foto-assessment</h1>
        <p>
          Upload foto's van de ruimte. Wij beoordelen de veiligheid en sturen je direct een persoonlijk
          verbeterplan met concrete adviezen.
        </p>
      </section>

      <section className="grid grid-3">
        <article className="card room-card assessment-room-card">
          <h2>Badkamercheck: veilig en toegankelijk</h2>
          <p className="muted">
            Wij bekijken waar het veiliger en makkelijker kan, bijvoorbeeld bij uitglijden, opstaan en instappen.
            Daarna krijg je een helder advies met verbeterpunten en opties voor aanpassingen.
          </p>
          <Link className="btn" href="/assessment/new?room=bathroom">
            Start badkamercheck
          </Link>
        </article>

        <article className="card room-card assessment-room-card">
          <h2>Trap en hal: voorkom struikelen</h2>
          <p className="muted">
            We letten op drempels, losse vloerkleden, verlichting en mogelijkheden voor extra houvast. Je ontvangt
            direct tips en een concreet verbeterplan.
          </p>
          <Link className="btn" href="/assessment/new?room=stairs_hall">
            Start trap en hal check
          </Link>
        </article>

        <article className="card room-card assessment-room-card">
          <h2>Woonkamercheck: veilig en comfortabel</h2>
          <p className="muted">
            We beoordelen waar de woonkamer veiliger en makkelijker kan. Je krijgt een helder advies met concrete
            verbeterpunten.
          </p>
          <Link className="btn" href="/woonkamer">
            Start woonkamercheck
          </Link>
        </article>

        <article className="card room-card assessment-room-card">
          <h2>Slaapkamercheck: veilig opstaan en lopen</h2>
          <p className="muted">
            We beoordelen waar de slaapkamer veiliger en makkelijker kan, vooral bij opstaan en 's nachts lopen. Je
            krijgt een concreet verbeterplan.
          </p>
          <Link className="btn" href="/slaapkamer">
            Start slaapkamercheck
          </Link>
        </article>

        <article className="card room-card assessment-room-card">
          <h2>Keukencheck: veilig en goed bereikbaar</h2>
          <p className="muted">
            We beoordelen waar de keuken veiliger en makkelijker kan tijdens lopen, pakken en koken. Je ontvangt
            praktische adviezen en verbeterpunten.
          </p>
          <Link className="btn" href="/keuken">
            Start keukencheck
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
