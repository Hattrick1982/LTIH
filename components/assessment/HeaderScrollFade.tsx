"use client";

import { useEffect } from "react";

const SCROLLED_CLASS = "mobile-header-scrolled";
const HIDE_THRESHOLD = 8;
const SHOW_THRESHOLD = 2;
const UNLOCK_AFTER_HIDE_MS = 450;

function setScrolledClass(isScrolled: boolean) {
  document.documentElement.classList.toggle(SCROLLED_CLASS, isScrolled);
  document.body.classList.toggle(SCROLLED_CLASS, isScrolled);
}

function getScrollTop() {
  return window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0;
}

export function HeaderScrollFade() {
  useEffect(() => {
    let ticking = false;
    let isHidden = getScrollTop() > HIDE_THRESHOLD;
    let hiddenLockUntil = 0;

    const updateScrollState = () => {
      const top = getScrollTop();
      const now = performance.now();

      if (!isHidden && top > HIDE_THRESHOLD) {
        isHidden = true;
        hiddenLockUntil = now + UNLOCK_AFTER_HIDE_MS;
        setScrolledClass(true);
      } else if (isHidden && now >= hiddenLockUntil && top <= SHOW_THRESHOLD) {
        isHidden = false;
        setScrolledClass(false);
      }

      ticking = false;
    };

    const onScroll = () => {
      if (ticking) {
        return;
      }

      ticking = true;
      window.requestAnimationFrame(updateScrollState);
    };

    setScrolledClass(isHidden);

    window.addEventListener("scroll", onScroll, { passive: true });
    document.addEventListener("scroll", onScroll, { passive: true, capture: true });

    return () => {
      window.removeEventListener("scroll", onScroll);
      document.removeEventListener("scroll", onScroll, true);
      document.documentElement.classList.remove(SCROLLED_CLASS);
      document.body.classList.remove(SCROLLED_CLASS);
    };
  }, []);

  return null;
}
