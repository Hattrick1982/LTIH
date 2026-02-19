import type { Metadata } from "next";
import { AppTopNav } from "@/components/navigation/AppTopNav";
import {
  TEXT_SCALE_CLASS_NORMAL,
  getTextScaleInitScript
} from "@/lib/accessibility/text-scale";
import "./globals.css";

export const metadata: Metadata = {
  title: "LangerThuisinHuis | Woonveiligheid via foto-assessment",
  description:
    "Inzicht in val- en struikelrisico's, met direct toepasbare verbeterpunten."
};

function resolveMainSiteUrl() {
  const configured = process.env.MAIN_SITE_URL?.trim();

  if (!configured) {
    return "https://langerthuisinhuis.nl";
  }

  if (configured.startsWith("http://") || configured.startsWith("https://")) {
    return configured;
  }

  return `https://${configured}`;
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  const mainSiteUrl = resolveMainSiteUrl();

  return (
    <html lang="nl" className={TEXT_SCALE_CLASS_NORMAL} suppressHydrationWarning>
      <head>
        <script dangerouslySetInnerHTML={{ __html: getTextScaleInitScript() }} />
      </head>

      <body>
        <div className="site-shell">
          <header className="app-header-shell no-print" role="banner">
            <AppTopNav mainSiteUrl={mainSiteUrl} />
          </header>

          <main className="container">{children}</main>
        </div>
      </body>
    </html>
  );
}
