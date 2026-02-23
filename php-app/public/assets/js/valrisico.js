(function () {
  function initStickyInstructionsButton() {
    var button = document.querySelector('[data-sticky-instructions-btn]');
    var firstBlock = document.querySelector('[data-first-exercise-block]');

    if (!button || !firstBlock) {
      return;
    }

    function hideButton(hidden) {
      button.classList.toggle('is-hidden', hidden);
    }

    button.addEventListener('click', function () {
      firstBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            hideButton(entry.isIntersecting);
          });
        },
        {
          root: null,
          threshold: 0.15,
        }
      );

      observer.observe(firstBlock);
      return;
    }

    function onScrollFallback() {
      var rect = firstBlock.getBoundingClientRect();
      hideButton(rect.top < window.innerHeight && rect.bottom > 0);
    }

    window.addEventListener('scroll', onScrollFallback, { passive: true });
    onScrollFallback();
  }

  initStickyInstructionsButton();

  function initFeedbackModal() {
    var modal = document.querySelector('[data-feedback-modal]');
    if (!modal) {
      return;
    }

    var closeButton = modal.querySelector('[data-modal-close]');
    var isClosed = false;

    function closeModal() {
      if (isClosed) {
        return;
      }

      isClosed = true;
      modal.classList.add('is-hidden');
      modal.setAttribute('aria-hidden', 'true');
    }

    if (closeButton) {
      closeButton.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeModal();
      }
    });

    var firstInteractive = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (firstInteractive && typeof firstInteractive.focus === 'function') {
      firstInteractive.focus();
    }
  }

  initFeedbackModal();

  function initLooptestModeHint() {
    var modeInputs = document.querySelectorAll('[data-looptest-mode]');
    var aloneNote = document.getElementById('looptest-alone-note');

    if (!modeInputs.length || !aloneNote) {
      return;
    }

    function updateNote() {
      var selected = document.querySelector('[data-looptest-mode]:checked');
      aloneNote.hidden = !(selected && selected.value === 'alone');
    }

    modeInputs.forEach(function (input) {
      input.addEventListener('change', updateNote);
    });

    updateNote();
  }

  initLooptestModeHint();

  var wrap = document.querySelector('[data-video-wrap]');
  if (!wrap) {
    return;
  }

  var video = wrap.querySelector('[data-valrisico-video]');
  if (!video) {
    return;
  }

  wrap.querySelectorAll('[data-video-action]').forEach(function (button) {
    button.addEventListener('click', function () {
      var action = button.getAttribute('data-video-action');

      if (action === 'play') {
        if (video.paused) {
          video.play();
        } else {
          video.pause();
        }
      }

      if (action === 'replay') {
        video.currentTime = 0;
        video.play();
      }

      if (action === 'slow') {
        video.playbackRate = video.playbackRate === 0.75 ? 1 : 0.75;
        button.textContent = video.playbackRate === 0.75 ? 'Normale snelheid' : 'Langzamer afspelen';
      }
    });
  });
})();
