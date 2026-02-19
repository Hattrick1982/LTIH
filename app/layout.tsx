import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "LangerThuisinHuis | Foto-assessment Woonveiligheid",
  description: "Analyseer valrisico's in huis op basis van foto's."
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="nl">
      <body>
        <main className="container">{children}</main>
      </body>
    </html>
  );
}
