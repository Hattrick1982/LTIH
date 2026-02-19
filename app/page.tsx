import Link from "next/link";

export default function HomePage() {
  return (
    <div className="grid" style={{ gap: "1.4rem" }}>
      <section className="hero">
        <p className="kicker">Woonveiligheidsscan</p>
        <h1>Start je foto-assessment</h1>
        <p>
          Upload foto's van de ruimte. Wij beoordelen de veiligheid en sturen je direct een persoonlijk verbeterplan met concrete adviezen.
        </p>
        <div className="actions">
          <Link href="/assessment" className="btn">
            Start je foto-assessment
          </Link>
          <a className="btn btn-secondary" href="https://www.langerthuisinhuis.nl" target="_blank" rel="noreferrer">
            Naar langerthuisinhuis.nl
          </a>
        </div>
      </section>
    </div>
  );
}
