(() => {
  'use strict';

  const rail = document.querySelector('#aisle-rail');
  if (!rail) return;

  function normalizeAisleButtons() {
    rail.querySelectorAll('.aisle-button').forEach((button) => {
      const selected = button.getAttribute('aria-selected') === 'true';
      button.removeAttribute('role');
      button.removeAttribute('aria-selected');
      button.setAttribute('aria-pressed', selected ? 'true' : 'false');
      button.style.background = selected ? 'var(--rail)' : '';
      button.style.color = selected ? '#fff' : '';
      button.style.borderColor = selected ? 'var(--rail)' : '';
    });
  }

  const observer = new MutationObserver(normalizeAisleButtons);
  observer.observe(rail, { childList: true });
  normalizeAisleButtons();
})();
