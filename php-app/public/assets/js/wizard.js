(function () {
  var form = document.getElementById('assessment-wizard');
  if (!form) {
    return;
  }

  var MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;
  var ALLOWED_TYPES = ['image/jpeg', 'image/png'];

  var roomType = form.getAttribute('data-room-type') || '';
  var minPhotos = Number(form.getAttribute('data-min-photos') || 2);
  var maxPhotos = Number(form.getAttribute('data-max-photos') || 5);

  var consent = document.getElementById('consent-checkbox');
  var submitButton = document.getElementById('wizard-submit');
  var globalError = document.getElementById('wizard-error');
  var selectedCountEl = document.getElementById('selected-count');
  var progressHintEl = document.getElementById('progress-hint');

  var state = new Map();

  function parseJsonResponse(response, phaseLabel) {
    return response.text().then(function (text) {
      var data = null;

      try {
        data = text ? JSON.parse(text) : {};
      } catch (error) {
        throw new Error('Server gaf geen geldige JSON terug tijdens ' + phaseLabel + '. Controleer serverlogs in je terminal.');
      }

      return {
        ok: response.ok,
        status: response.status,
        data: data,
      };
    });
  }

  function setGlobalError(message) {
    if (!globalError) {
      return;
    }
    if (!message) {
      globalError.hidden = true;
      globalError.textContent = '';
      return;
    }
    globalError.textContent = message;
    globalError.hidden = false;
  }

  function countSelected() {
    var count = 0;
    state.forEach(function (item) {
      if (item.file) {
        count += 1;
      }
    });
    return count;
  }

  function updateProgress() {
    var selected = countSelected();
    if (selectedCountEl) {
      selectedCountEl.textContent = String(selected);
    }

    if (progressHintEl) {
      if (selected < minPhotos) {
        progressHintEl.textContent = 'Minimaal ' + minPhotos + " foto's nodig om door te gaan.";
      } else {
        progressHintEl.textContent = 'Minimaal gehaald. Voeg tot ' + maxPhotos + " foto's toe voor beter advies.";
      }
    }

    var canSubmit = consent && consent.checked && selected >= minPhotos && selected <= maxPhotos && !submitButton.dataset.loading;
    submitButton.disabled = !canSubmit;
  }

  function setItemError(itemId, message) {
    var li = form.querySelector('li[data-item-id="' + itemId + '"]');
    if (!li) {
      return;
    }

    var errorEl = li.querySelector('.prompt-item-error');
    if (!errorEl) {
      return;
    }

    if (!message) {
      errorEl.hidden = true;
      errorEl.textContent = '';
      return;
    }

    errorEl.textContent = message;
    errorEl.hidden = false;
  }

  function validateFile(file) {
    if (ALLOWED_TYPES.indexOf(file.type) === -1) {
      return 'Ongeldig bestandstype. Gebruik alleen JPG of PNG.';
    }

    if (file.size > MAX_FILE_SIZE_BYTES) {
      return 'Bestand te groot. Maximaal 10MB per foto.';
    }

    return null;
  }

  function updateItemPreview(itemId, file) {
    var li = form.querySelector('li[data-item-id="' + itemId + '"]');
    if (!li) {
      return;
    }

    var thumb = li.querySelector('.prompt-thumb');
    var remove = li.querySelector('.js-remove');

    if (!thumb || !remove) {
      return;
    }

    var prev = state.get(itemId);
    if (prev && prev.previewUrl) {
      URL.revokeObjectURL(prev.previewUrl);
    }

    if (!file) {
      thumb.hidden = true;
      thumb.src = '';
      thumb.alt = '';
      remove.hidden = true;
      state.set(itemId, { file: null, previewUrl: null });
      return;
    }

    var url = URL.createObjectURL(file);
    thumb.hidden = false;
    thumb.src = url;
    thumb.alt = 'Preview foto';
    remove.hidden = false;

    state.set(itemId, { file: file, previewUrl: url });
  }

  form.querySelectorAll('li[data-item-id]').forEach(function (li) {
    var itemId = li.getAttribute('data-item-id');
    if (!itemId) {
      return;
    }

    state.set(itemId, { file: null, previewUrl: null });

    var cameraInput = li.querySelector('.js-camera-input');
    var galleryInput = li.querySelector('.js-gallery-input');
    var openCamera = li.querySelector('.js-open-camera');
    var openGallery = li.querySelector('.js-open-gallery');
    var remove = li.querySelector('.js-remove');

    var onFileChange = function (event) {
      var file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
      event.target.value = '';

      if (!file) {
        return;
      }

      var selectedCount = countSelected();
      var current = state.get(itemId);

      if ((!current || !current.file) && selectedCount >= maxPhotos) {
        setItemError(itemId, 'Je kunt maximaal ' + maxPhotos + " foto's toevoegen.");
        return;
      }

      var validationError = validateFile(file);
      if (validationError) {
        setItemError(itemId, validationError);
        return;
      }

      setItemError(itemId, null);
      setGlobalError(null);
      updateItemPreview(itemId, file);
      updateProgress();
    };

    if (cameraInput) {
      cameraInput.addEventListener('change', onFileChange);
    }

    if (galleryInput) {
      galleryInput.addEventListener('change', onFileChange);
    }

    if (openCamera && cameraInput) {
      openCamera.addEventListener('click', function () {
        cameraInput.click();
      });
    }

    if (openGallery && galleryInput) {
      openGallery.addEventListener('click', function () {
        galleryInput.click();
      });
    }

    if (remove) {
      remove.addEventListener('click', function () {
        setItemError(itemId, null);
        updateItemPreview(itemId, null);
        updateProgress();
      });
    }
  });

  if (consent) {
    consent.addEventListener('change', updateProgress);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    setGlobalError(null);

    if (!consent || !consent.checked) {
      setGlobalError('Geef eerst toestemming voor verwerking van de foto\'s.');
      return;
    }

    var selectedFiles = [];
    state.forEach(function (entry) {
      if (entry.file) {
        selectedFiles.push(entry.file);
      }
    });

    if (selectedFiles.length < minPhotos) {
      setGlobalError('Upload minimaal ' + minPhotos + " foto's om te starten. Voor de beste analyse adviseren we " + maxPhotos + " foto's.");
      return;
    }

    submitButton.dataset.loading = '1';
    submitButton.textContent = 'Analyse gestart, dit kan een paar minuten duren';
    updateProgress();

    var formData = new FormData();
    selectedFiles.forEach(function (file) {
      formData.append('files[]', file);
    });

    fetch('/api/assessment/upload', {
      method: 'POST',
      body: formData,
    })
      .catch(function () {
        throw new Error('Netwerkfout tijdens upload. Controleer of `php -S 127.0.0.1:8080 -t public` nog draait.');
      })
      .then(function (response) {
        return parseJsonResponse(response, 'upload');
      })
      .then(function (uploadResult) {
        if (!uploadResult.ok) {
          throw new Error(uploadResult.data.error || 'Upload mislukt.');
        }

        var fileIds = (uploadResult.data.files || []).map(function (file) {
          return file.file_id;
        });

        return fetch('/api/assessment/analyze', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            room_type: roomType,
            file_ids: fileIds,
          }),
        });
      })
      .catch(function () {
        throw new Error('Netwerkfout tijdens analyse. Controleer of de server nog draait en kijk naar PHP-fouten in je terminal.');
      })
      .then(function (response) {
        return parseJsonResponse(response, 'analyse');
      })
      .then(function (analysisResult) {
        if (!analysisResult.ok) {
          var details = Array.isArray(analysisResult.data.details) ? analysisResult.data.details.join(' ') : '';
          var message = analysisResult.data.error || 'Analyse mislukt.';
          throw new Error((message + ' ' + details).trim());
        }

        window.location.href = '/assessment/result/' + encodeURIComponent(analysisResult.data.assessment_id);
      })
      .catch(function (error) {
        setGlobalError(error instanceof Error ? error.message : 'Er ging iets mis. Probeer opnieuw.');
      })
      .finally(function () {
        delete submitButton.dataset.loading;
        submitButton.textContent = 'Analyse starten';
        updateProgress();
      });
  });

  updateProgress();
})();
