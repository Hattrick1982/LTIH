import Link from "next/link";

export default function HomePage() {
  return (
    <div className="grid" style={{ gap: "1.5rem" }}>
      <section className="card">
        <h1>Foto-assessment Woonveiligheid</h1>
        <p className="muted">
          Upload foto's van een ruimte en ontvang een risico-analyse met praktisch verbeterplan.
        </p>
        <Link href="/assessment" className="btn" style={{ display: "inline-block" }}>
          Start assessment
        </Link>
      </section>
    </div>
  );
}
