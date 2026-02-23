(function () {
  document.querySelectorAll('[data-issues-accordion] .issue-accordion-trigger').forEach(function (button) {
    button.addEventListener('click', function () {
      var panelId = button.getAttribute('aria-controls');
      if (!panelId) {
        return;
      }

      var panel = document.getElementById(panelId);
      if (!panel) {
        return;
      }

      var expanded = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      panel.classList.toggle('open', !expanded);

      var chevron = button.querySelector('.issue-chevron');
      if (chevron) {
        chevron.classList.toggle('open', !expanded);
      }
    });
  });

  var deleteButton = document.querySelector('[data-delete-assessment]');
  var deleteError = document.getElementById('delete-error');

  if (deleteButton) {
    deleteButton.addEventListener('click', function () {
      var assessmentId = deleteButton.getAttribute('data-assessment-id');
      if (!assessmentId) {
        return;
      }

      var confirmed = window.confirm("Weet je zeker dat je deze analyse en foto's wilt verwijderen?");
      if (!confirmed) {
        return;
      }

      deleteButton.disabled = true;
      deleteButton.textContent = 'Verwijderen...';

      fetch('/api/assessment/' + encodeURIComponent(assessmentId), {
        method: 'DELETE',
      })
        .then(function (response) {
          return response.json().then(function (data) {
            return { ok: response.ok, data: data };
          });
        })
        .then(function (result) {
          if (!result.ok) {
            throw new Error(result.data.error || 'Verwijderen mislukt.');
          }

          window.location.href = '/assessment';
        })
        .catch(function (error) {
          if (deleteError) {
            deleteError.hidden = false;
            deleteError.textContent = error instanceof Error ? error.message : 'Onbekende fout bij verwijderen.';
          }
          deleteButton.disabled = false;
          deleteButton.textContent = "Verwijder analyse en foto's";
        });
    });
  }

  document.querySelectorAll('[data-print-now]').forEach(function (button) {
    button.addEventListener('click', function () {
      window.print();
    });
  });
})();
