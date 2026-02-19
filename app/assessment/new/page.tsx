import Link from "next/link";
import { AssessmentWizard } from "@/components/assessment/AssessmentWizard";
import { isRoomType } from "@/lib/assessment/room-config";

export const dynamic = "force-dynamic";

export default async function NewAssessmentPage({
  searchParams
}: {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}) {
  const params = await searchParams;
  const roomParam = typeof params.room === "string" ? params.room : "";

  if (!isRoomType(roomParam)) {
    return (
      <section className="card">
        <h1>Ongeldige ruimtekeuze</h1>
        <p>Kies een geldige ruimte om door te gaan.</p>
        <Link href="/assessment" className="btn">
          Terug naar ruimtekeuze
        </Link>
      </section>
    );
  }

  return <AssessmentWizard roomType={roomParam} />;
}
