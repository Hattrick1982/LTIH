"use client";

import Link from "next/link";
import { usePathname, useSearchParams } from "next/navigation";
import { useEffect, useMemo, useState } from "react";

type MobileNavItem = {
  href: string;
  label: string;
};

const MOBILE_NAV_ITEMS: MobileNavItem[] = [
  { href: "/assessment", label: "Start" },
  { href: "/assessment/new?room=bathroom", label: "Badkamercheck" },
  { href: "/assessment/new?room=stairs_hall", label: "Trap en hal" },
  { href: "/woonkamer", label: "Woonkamer" },
  { href: "/slaapkamer", label: "Slaapkamer" },
  { href: "/keuken", label: "Keuken" },
  { href: "/contact", label: "Adviesgesprek" }
];

function matchesRoute(targetHref: string, pathname: string, searchParams: URLSearchParams) {
  const [targetPath, queryString = ""] = targetHref.split("?");

  if (pathname !== targetPath) {
    return false;
  }

  if (!queryString) {
    return true;
  }

  const expectedParams = new URLSearchParams(queryString);

  for (const [key, value] of expectedParams.entries()) {
    if (searchParams.get(key) !== value) {
      return false;
    }
  }

  return true;
}

export function MobileNavMenu() {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [isOpen, setIsOpen] = useState(false);
  const queryString = searchParams.toString();

  const urlParams = useMemo(() => new URLSearchParams(queryString), [queryString]);

  useEffect(() => {
    setIsOpen(false);
  }, [pathname, queryString]);

  useEffect(() => {
    document.body.style.overflow = isOpen ? "hidden" : "";

    return () => {
      document.body.style.overflow = "";
    };
  }, [isOpen]);

  useEffect(() => {
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsOpen(false);
      }
    };

    window.addEventListener("keydown", onKeyDown);

    return () => {
      window.removeEventListener("keydown", onKeyDown);
    };
  }, []);

  return (
    <div className="mobile-nav" aria-label="Mobiel menu">
      <button
        type="button"
        className="mobile-menu-toggle"
        aria-expanded={isOpen}
        aria-controls="mobile-nav-panel"
        onClick={() => setIsOpen((prev) => !prev)}
      >
        <span className="mobile-menu-icon" aria-hidden="true" />
        <span>Menu</span>
      </button>

      <button
        type="button"
        aria-label="Sluit menu"
        className={`mobile-nav-overlay ${isOpen ? "open" : ""}`}
        onClick={() => setIsOpen(false)}
      />

      <aside
        id="mobile-nav-panel"
        className={`mobile-nav-panel ${isOpen ? "open" : ""}`}
        aria-hidden={!isOpen}
      >
        <div className="mobile-nav-panel-header">
          <strong>Navigatie</strong>
          <button
            type="button"
            className="mobile-nav-close"
            onClick={() => setIsOpen(false)}
            aria-label="Sluit menu"
          >
            Sluiten
          </button>
        </div>

        <nav className="mobile-nav-links" aria-label="Mobiele navigatie links">
          {MOBILE_NAV_ITEMS.map((item) => {
            const isActive = matchesRoute(item.href, pathname, urlParams);

            return (
              <Link
                key={item.href}
                href={item.href}
                className={`mobile-nav-link ${isActive ? "active" : ""}`}
                onClick={() => setIsOpen(false)}
              >
                {item.label}
              </Link>
            );
          })}

          <a
            href="https://www.langerthuisinhuis.nl"
            target="_blank"
            rel="noreferrer"
            className="mobile-nav-home"
            onClick={() => setIsOpen(false)}
          >
            Home
          </a>
        </nav>
      </aside>
    </div>
  );
}
