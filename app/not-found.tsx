import Link from "next/link";

export default function NotFoundPage() {
  return (
    <section className="card">
      <h1>Pagina niet gevonden</h1>
      <p className="muted">De gevraagde assessment bestaat niet (meer) of is verwijderd.</p>
      <Link href="/assessment" className="btn">
        Terug naar assessment
      </Link>
    </section>
  );
}
