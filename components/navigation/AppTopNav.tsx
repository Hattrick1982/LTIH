"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname, useSearchParams } from "next/navigation";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { TextSizeToggle } from "@/components/navigation/TextSizeToggle";
import { ROOM_NAV_ITEMS, getActiveRoomKey } from "@/lib/navigation/room-nav";

type AppTopNavProps = {
  mainSiteUrl: string;
};

function ExternalLinkIcon() {
  return (
    <svg viewBox="0 0 16 16" aria-hidden="true" className="topnav-external-icon">
      <path
        d="M6 3.5h6.5V10m-.4-6.1L5.3 10.7M13 12.5H3V2.5h4"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

function ChevronIcon({ open }: { open: boolean }) {
  return (
    <svg
      viewBox="0 0 16 16"
      aria-hidden="true"
      className={`topnav-chevron ${open ? "open" : ""}`}
    >
      <path
        d="M3.6 5.9L8 10.1l4.4-4.2"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.6"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

export function AppTopNav({ mainSiteUrl }: AppTopNavProps) {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const queryString = searchParams.toString();

  const urlParams = useMemo(() => new URLSearchParams(queryString), [queryString]);
  const activeRoomKey = useMemo(() => getActiveRoomKey(pathname, urlParams), [pathname, urlParams]);

  const isStartActive = pathname === "/" || pathname === "/assessment";
  const isRoomsActive = Boolean(activeRoomKey);
  const isAdviceActive = pathname === "/contact";

  const [isDesktopDropdownOpen, setDesktopDropdownOpen] = useState(false);
  const [isMobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [isMobileRoomsOpen, setMobileRoomsOpen] = useState(false);

  const dropdownRef = useRef<HTMLDivElement | null>(null);
  const desktopCloseTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const clearDesktopCloseTimer = useCallback(() => {
    if (desktopCloseTimerRef.current) {
      clearTimeout(desktopCloseTimerRef.current);
      desktopCloseTimerRef.current = null;
    }
  }, []);

  const openDesktopDropdown = useCallback(() => {
    clearDesktopCloseTimer();
    setDesktopDropdownOpen(true);
  }, [clearDesktopCloseTimer]);

  const scheduleDesktopDropdownClose = useCallback(() => {
    clearDesktopCloseTimer();
    desktopCloseTimerRef.current = setTimeout(() => {
      setDesktopDropdownOpen(false);
      desktopCloseTimerRef.current = null;
    }, 140);
  }, [clearDesktopCloseTimer]);

  useEffect(() => {
    clearDesktopCloseTimer();
    setDesktopDropdownOpen(false);
    setMobileMenuOpen(false);
    setMobileRoomsOpen(Boolean(activeRoomKey));
  }, [pathname, queryString, activeRoomKey, clearDesktopCloseTimer]);

  useEffect(() => {
    if (!isMobileMenuOpen) {
      document.body.style.overflow = "";
      return;
    }

    document.body.style.overflow = "hidden";

    return () => {
      document.body.style.overflow = "";
    };
  }, [isMobileMenuOpen]);

  useEffect(() => {
    const onWindowKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        clearDesktopCloseTimer();
        setDesktopDropdownOpen(false);
        setMobileMenuOpen(false);
      }
    };

    const onWindowClick = (event: MouseEvent) => {
      if (!dropdownRef.current) {
        return;
      }

      if (event.target instanceof Node && !dropdownRef.current.contains(event.target)) {
        clearDesktopCloseTimer();
        setDesktopDropdownOpen(false);
      }
    };

    window.addEventListener("keydown", onWindowKeyDown);
    window.addEventListener("click", onWindowClick);

    return () => {
      window.removeEventListener("keydown", onWindowKeyDown);
      window.removeEventListener("click", onWindowClick);
    };
  }, [clearDesktopCloseTimer]);

  useEffect(() => {
    return () => {
      clearDesktopCloseTimer();
    };
  }, [clearDesktopCloseTimer]);

  return (
    <>
      <div className="topnav-inner">
        <Link href="/assessment" className="topnav-logo" aria-label="Naar start van foto-assessment">
          <Image
            src="/ltih-logo.png"
            alt="LangerThuisinHuis"
            width={228}
            height={140}
            priority
            unoptimized
            className="topnav-logo-image"
          />
        </Link>

        <nav className="topnav-desktop" aria-label="Hoofdnavigatie">
          <Link href="/assessment" className={`topnav-link ${isStartActive ? "active" : ""}`}>
            Start
          </Link>

          <div
            className="topnav-dropdown"
            ref={dropdownRef}
            onMouseEnter={openDesktopDropdown}
            onMouseLeave={scheduleDesktopDropdownClose}
          >
            <button
              type="button"
              className={`topnav-link topnav-dropdown-trigger ${isRoomsActive ? "active" : ""}`}
              aria-expanded={isDesktopDropdownOpen}
              aria-controls="desktop-rooms-menu"
              onClick={() => {
                clearDesktopCloseTimer();
                setDesktopDropdownOpen((open) => !open);
              }}
            >
              <span>Ruimtes</span>
              <ChevronIcon open={isDesktopDropdownOpen} />
            </button>

            <div
              id="desktop-rooms-menu"
              className={`topnav-dropdown-menu ${isDesktopDropdownOpen ? "open" : ""}`}
              aria-label="Ruimtekeuze"
              onMouseEnter={openDesktopDropdown}
              onMouseLeave={scheduleDesktopDropdownClose}
            >
              {ROOM_NAV_ITEMS.map((room) => {
                const isActive = activeRoomKey === room.roomKey;

                return (
                  <Link
                    key={room.roomKey}
                    href={room.href}
                    className={`topnav-dropdown-item ${isActive ? "active" : ""}`}
                    onClick={() => {
                      clearDesktopCloseTimer();
                      setDesktopDropdownOpen(false);
                    }}
                  >
                    <span className="topnav-dropdown-title">{room.label}</span>
                    <span className="topnav-dropdown-hint">{room.hint}</span>
                  </Link>
                );
              })}
            </div>
          </div>

          <Link href="/contact" className={`topnav-link ${isAdviceActive ? "active" : ""}`}>
            Adviesgesprek
          </Link>
        </nav>

        <div className="topnav-actions">
          <TextSizeToggle label="Tekst" className="topnav-text-size-desktop" />

          <a
            href={mainSiteUrl}
            target="_blank"
            rel="noopener noreferrer"
            title="Opent in nieuw tabblad"
            className="topnav-external-link"
            aria-label="LangerThuisinHuis.nl (opent in nieuw tabblad)"
          >
            <span>LangerThuisinHuis.nl</span>
            <ExternalLinkIcon />
          </a>
        </div>

        <button
          type="button"
          className="topnav-mobile-toggle"
          aria-expanded={isMobileMenuOpen}
          aria-controls="mobile-main-menu"
          aria-label="Open navigatiemenu"
          onClick={() => setMobileMenuOpen((open) => !open)}
        >
          <span className="topnav-mobile-toggle-bars" aria-hidden="true" />
          <span>Menu</span>
        </button>
      </div>

      <button
        type="button"
        className={`topnav-mobile-overlay ${isMobileMenuOpen ? "open" : ""}`}
        onClick={() => setMobileMenuOpen(false)}
        aria-label="Sluit mobiel menu"
      />

      <aside
        id="mobile-main-menu"
        className={`topnav-mobile-panel ${isMobileMenuOpen ? "open" : ""}`}
        aria-hidden={!isMobileMenuOpen}
      >
        <div className="topnav-mobile-panel-header">
          <strong>Navigatie</strong>
          <button
            type="button"
            className="topnav-mobile-close"
            onClick={() => setMobileMenuOpen(false)}
            aria-label="Sluit menu"
          >
            Sluiten
          </button>
        </div>

        <TextSizeToggle label="Tekstgrootte" className="topnav-text-size-mobile" />

        <nav className="topnav-mobile-links" aria-label="Mobiele hoofdnavigatie">
          <Link
            href="/assessment"
            className={`topnav-mobile-link ${isStartActive ? "active" : ""}`}
            onClick={() => setMobileMenuOpen(false)}
          >
            Start
          </Link>

          <section className="topnav-mobile-rooms">
            <button
              type="button"
              className={`topnav-mobile-link topnav-mobile-rooms-toggle ${isRoomsActive ? "active" : ""}`}
              aria-expanded={isMobileRoomsOpen}
              aria-controls="mobile-rooms-panel"
              onClick={() => setMobileRoomsOpen((open) => !open)}
            >
              <span>Ruimtes</span>
              <ChevronIcon open={isMobileRoomsOpen} />
            </button>

            <div
              id="mobile-rooms-panel"
              className={`topnav-mobile-rooms-panel ${isMobileRoomsOpen ? "open" : ""}`}
            >
              {ROOM_NAV_ITEMS.map((room) => {
                const isActive = activeRoomKey === room.roomKey;

                return (
                  <Link
                    key={room.roomKey}
                    href={room.href}
                    className={`topnav-mobile-room-item ${isActive ? "active" : ""}`}
                    onClick={() => setMobileMenuOpen(false)}
                  >
                    <span className="topnav-mobile-room-title">{room.label}</span>
                    <span className="topnav-mobile-room-hint">{room.hint}</span>
                  </Link>
                );
              })}
            </div>
          </section>

          <Link
            href="/contact"
            className={`topnav-mobile-link ${isAdviceActive ? "active" : ""}`}
            onClick={() => setMobileMenuOpen(false)}
          >
            Adviesgesprek
          </Link>

          <a
            href={mainSiteUrl}
            target="_blank"
            rel="noopener noreferrer"
            title="Opent in nieuw tabblad"
            className="topnav-mobile-external"
            onClick={() => setMobileMenuOpen(false)}
          >
            <span>LangerThuisinHuis.nl</span>
            <ExternalLinkIcon />
          </a>
        </nav>
      </aside>
    </>
  );
}
