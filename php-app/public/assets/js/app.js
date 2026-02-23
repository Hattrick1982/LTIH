(function () {
  var TEXT_SCALE_KEY = 'lthih_text_scale';

  function applyScale(scale) {
    var root = document.documentElement;
    root.classList.remove('text-scale-normal', 'text-scale-large');
    root.classList.add(scale === 'large' ? 'text-scale-large' : 'text-scale-normal');

    document.querySelectorAll('[data-text-size-root]').forEach(function (wrapper) {
      wrapper.querySelectorAll('[data-text-scale]').forEach(function (btn) {
        var isActive = btn.getAttribute('data-text-scale') === scale;
        btn.classList.toggle('active', isActive);
        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      });
    });
  }

  function getStoredScale() {
    try {
      var value = window.localStorage.getItem(TEXT_SCALE_KEY);
      return value === 'large' ? 'large' : 'normal';
    } catch (error) {
      return 'normal';
    }
  }

  function setStoredScale(scale) {
    try {
      window.localStorage.setItem(TEXT_SCALE_KEY, scale);
    } catch (error) {
      // No-op
    }
  }

  function initTextScale() {
    var current = getStoredScale();
    applyScale(current);

    document.querySelectorAll('[data-text-scale]').forEach(function (button) {
      button.addEventListener('click', function () {
        var scale = button.getAttribute('data-text-scale') === 'large' ? 'large' : 'normal';
        setStoredScale(scale);
        applyScale(scale);
      });
    });
  }

  function initDesktopDropdown() {
    var dropdown = document.querySelector('[data-desktop-dropdown]');
    if (!dropdown) {
      return;
    }

    var trigger = dropdown.querySelector('.topnav-dropdown-trigger');
    var menu = dropdown.querySelector('.topnav-dropdown-menu');
    if (!trigger || !menu) {
      return;
    }

    function open() {
      trigger.setAttribute('aria-expanded', 'true');
      menu.classList.add('open');
      var chevron = trigger.querySelector('.topnav-chevron');
      if (chevron) {
        chevron.classList.add('open');
      }
    }

    function close() {
      trigger.setAttribute('aria-expanded', 'false');
      menu.classList.remove('open');
      var chevron = trigger.querySelector('.topnav-chevron');
      if (chevron) {
        chevron.classList.remove('open');
      }
    }

    trigger.addEventListener('click', function () {
      var expanded = trigger.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        close();
      } else {
        open();
      }
    });

    dropdown.addEventListener('mouseenter', open);
    dropdown.addEventListener('mouseleave', close);

    document.addEventListener('click', function (event) {
      if (!dropdown.contains(event.target)) {
        close();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        close();
      }
    });
  }

  function initMobileMenu() {
    var toggle = document.querySelector('[data-mobile-menu-toggle]');
    var closeBtn = document.querySelector('[data-mobile-menu-close]');
    var panel = document.querySelector('[data-mobile-panel]');
    var overlay = document.querySelector('[data-mobile-overlay]');

    if (!toggle || !panel || !overlay) {
      return;
    }

    function setOpen(open) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');
      panel.classList.toggle('open', open);
      overlay.classList.toggle('open', open);
      document.body.style.overflow = open ? 'hidden' : '';
    }

    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') === 'true';
      setOpen(!open);
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        setOpen(false);
      });
    }

    overlay.addEventListener('click', function () {
      setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        setOpen(false);
      }
    });

    panel.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        setOpen(false);
      });
    });
  }

  initTextScale();
  initDesktopDropdown();
  initMobileMenu();
})();
