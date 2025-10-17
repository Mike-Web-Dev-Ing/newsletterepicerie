// Gestion simple du consentement cookies
(function () {
  const STORAGE_KEY = 'cookieConsent';
  const OPTIONAL_CATEGORIES = ['analytics', 'marketing'];

  function $(selector, parent) {
    return (parent || document).querySelector(selector);
  }

  function $all(selector, parent) {
    return Array.from((parent || document).querySelectorAll(selector));
  }

  function loadPreferences() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (err) {
      console.warn('Impossible de lire les préférences cookies', err);
      return null;
    }
  }

  function persistPreferences(consent) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
    } catch (err) {
      console.warn('Impossible d\'enregistrer les préférences cookies', err);
    }
  }

  function dispatchConsentEvent(consent) {
    window.dispatchEvent(
      new CustomEvent('cookie-consent-change', { detail: consent })
    );
  }

  function ensurePreferencesShape(consent) {
    const base = {
      status: 'pending',
      categories: {},
      updatedAt: new Date().toISOString()
    };
    const merged = Object.assign(base, consent || {});
    OPTIONAL_CATEGORIES.forEach((cat) => {
      if (typeof merged.categories[cat] !== 'boolean') {
        merged.categories[cat] = false;
      }
    });
    return merged;
  }

  function setCheckboxes(consent, root) {
    OPTIONAL_CATEGORIES.forEach((cat) => {
      const cb = root.querySelector(`input[data-cc-category="${cat}"]`);
      if (cb) {
        cb.checked = Boolean(consent.categories[cat]);
      }
    });
  }

  function collectCheckboxes(root) {
    const values = {};
    OPTIONAL_CATEGORIES.forEach((cat) => {
      const cb = root.querySelector(`input[data-cc-category="${cat}"]`);
      values[cat] = cb ? cb.checked : false;
    });
    return values;
  }

  function toggleBanner(show, banner) {
    if (!banner) return;
    banner.classList.toggle('cookie-banner--hidden', !show);
    banner.setAttribute('aria-hidden', String(!show));
  }

  function togglePreferences(open, panel) {
    if (!panel) return;
    panel.classList.toggle('cookie-preferences--open', open);
    panel.setAttribute('aria-hidden', String(!open));
    document.body.classList.toggle('cookie-preferences-open', open);

    const dialog = $('.cookie-preferences__dialog', panel);
    if (open && dialog) {
      dialog.focus();
    }
  }

  function acceptAll(banner, panel) {
    const consent = ensurePreferencesShape({
      status: 'accepted',
      categories: OPTIONAL_CATEGORIES.reduce((acc, key) => {
        acc[key] = true;
        return acc;
      }, {})
    });
    persistPreferences(consent);
    dispatchConsentEvent(consent);
    toggleBanner(false, banner);
    togglePreferences(false, panel);
  }

  function rejectAll(banner, panel) {
    const consent = ensurePreferencesShape({
      status: 'rejected',
      categories: OPTIONAL_CATEGORIES.reduce((acc, key) => {
        acc[key] = false;
        return acc;
      }, {})
    });
    persistPreferences(consent);
    dispatchConsentEvent(consent);
    toggleBanner(false, banner);
    togglePreferences(false, panel);
  }

  function savePreferences(banner, panel) {
    const categories = collectCheckboxes(panel);
    const consent = ensurePreferencesShape({
      status: 'custom',
      categories
    });
    persistPreferences(consent);
    dispatchConsentEvent(consent);
    toggleBanner(false, banner);
    togglePreferences(false, panel);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const banner = $('#cookie-banner');
    const panel = $('#cookie-preferences');
    if (!banner || !panel) return;

    const btnAccept = $('[data-cc-accept]', banner);
    const btnOpenPrefs = $('[data-cc-open-preferences]', banner);
    const btnSave = $('[data-cc-save]', panel);
    const btnReject = $('[data-cc-reject]', panel);
    const closeToggles = $all('[data-cc-close]', panel);

    const consent = ensurePreferencesShape(loadPreferences());
    const hasAction = consent.status && consent.status !== 'pending';

    if (hasAction) {
      toggleBanner(false, banner);
      setCheckboxes(consent, panel);
    } else {
      toggleBanner(true, banner);
    }

    btnAccept &&
      btnAccept.addEventListener('click', function () {
        acceptAll(banner, panel);
      });

    btnOpenPrefs &&
      btnOpenPrefs.addEventListener('click', function () {
        const current = ensurePreferencesShape(loadPreferences());
        setCheckboxes(current, panel);
        togglePreferences(true, panel);
      });

    btnSave &&
      btnSave.addEventListener('click', function () {
        savePreferences(banner, panel);
      });

    btnReject &&
      btnReject.addEventListener('click', function () {
        rejectAll(banner, panel);
      });

    closeToggles.forEach((node) =>
      node.addEventListener('click', function () {
        togglePreferences(false, panel);
      })
    );

    panel.addEventListener('keydown', function (evt) {
      if (evt.key === 'Escape') {
        togglePreferences(false, panel);
      }
    });
  });
})();
