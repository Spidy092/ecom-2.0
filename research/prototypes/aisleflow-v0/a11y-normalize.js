(() => {
  'use strict';

  const shell = document.querySelector('.prototype-shell');
  const rail = document.querySelector('#aisle-rail');
  const pulse = document.querySelector('#basket-pulse');
  const pulseMessage = document.querySelector('#pulse-message');
  const pulseItems = document.querySelector('#pulse-items');
  const pulseTotal = document.querySelector('#pulse-total');
  const resultsSummary = document.querySelector('.results-summary');
  const resultsCount = document.querySelector('#results-count');
  const resultsContext = document.querySelector('#results-context');
  const listPanel = document.querySelector('#list-panel');
  const cartPanel = document.querySelector('#cart-panel');
  const dock = document.querySelector('.mobile-dock');

  if (!shell || !rail) return;

  let lastSurfaceLauncher = null;
  let basketTimer = null;
  let searchTimer = null;
  let lastSearchAnnouncement = '';

  function makeLiveRegion(id) {
    const region = document.createElement('div');
    region.id = id;
    region.setAttribute('role', 'status');
    region.setAttribute('aria-live', 'polite');
    region.setAttribute('aria-atomic', 'true');
    Object.assign(region.style, {
      position: 'absolute',
      width: '1px',
      height: '1px',
      padding: '0',
      margin: '-1px',
      overflow: 'hidden',
      clip: 'rect(0, 0, 0, 0)',
      whiteSpace: 'nowrap',
      border: '0'
    });
    document.body.append(region);
    return region;
  }

  const basketLive = makeLiveRegion('a11y-basket-status');
  const searchLive = makeLiveRegion('a11y-search-status');

  function announce(region, message) {
    region.textContent = '';
    window.requestAnimationFrame(() => {
      region.textContent = message;
    });
  }

  function normalizeModeButtons() {
    document.querySelectorAll('[data-mode-button]').forEach((button) => {
      button.setAttribute('aria-pressed', button.classList.contains('is-active') ? 'true' : 'false');
    });
  }

  function normalizeAisleButtons() {
    rail.removeAttribute('role');
    rail.querySelectorAll('.aisle-button').forEach((button) => {
      const selected = button.getAttribute('aria-selected') === 'true' || button.getAttribute('aria-pressed') === 'true';
      button.removeAttribute('role');
      button.removeAttribute('aria-selected');
      button.setAttribute('aria-pressed', selected ? 'true' : 'false');
      button.style.background = selected ? 'var(--rail)' : '';
      button.style.color = selected ? '#fff' : '';
      button.style.borderColor = selected ? 'var(--rail)' : '';
    });
  }

  function normalizeShopperLanguage() {
    document.querySelectorAll('.add-button[aria-label]').forEach((button) => {
      const current = button.getAttribute('aria-label') || '';
      const next = current.replace(' to basket', ' to cart');
      if (next !== current) button.setAttribute('aria-label', next);
    });

    const cartEmpty = cartPanel?.querySelector('.empty-state');
    if (cartEmpty && /basket/i.test(cartEmpty.textContent || '')) {
      cartEmpty.textContent = 'Your cart is empty. Add groceries from the aisle ledger.';
    }

    if (pulseMessage) {
      const current = pulseMessage.textContent || '';
      let next = current.replace(/^Saved (.+) to your list$/, 'Saved $1 for later');
      next = next.replace(/^Removed (.+) from your list$/, 'Removed $1 from saved');
      if (next !== current) pulseMessage.textContent = next;
    }
  }

  function normalizeDynamicControls() {
    document.querySelectorAll('.quantity-control').forEach((group) => {
      group.setAttribute('role', 'group');
    });

    document.querySelectorAll('.qty-value[aria-live]').forEach((value) => {
      value.removeAttribute('aria-live');
    });

    document.querySelectorAll('.save-button').forEach((button) => {
      const saved = button.classList.contains('is-saved');
      const label = saved ? 'Remove from saved' : 'Save for later';
      button.removeAttribute('aria-pressed');
      if (button.textContent !== label) button.textContent = label;
      button.style.minHeight = '32px';
    });

    [listPanel, cartPanel].forEach((panel) => {
      if (panel) panel.setAttribute('tabindex', '-1');
    });
  }

  function normalizeLiveRegions() {
    if (pulse) {
      pulse.removeAttribute('role');
      pulse.removeAttribute('aria-live');
    }
    if (resultsSummary) {
      resultsSummary.removeAttribute('aria-live');
      resultsSummary.removeAttribute('role');
    }
  }

  function normalizeAll() {
    normalizeModeButtons();
    normalizeAisleButtons();
    normalizeDynamicControls();
    normalizeShopperLanguage();
    normalizeLiveRegions();
  }

  function announceBasketChange() {
    window.clearTimeout(basketTimer);
    basketTimer = window.setTimeout(() => {
      normalizeShopperLanguage();
      const message = pulseMessage?.textContent?.trim();
      if (!message) return;
      const items = pulseItems?.textContent?.trim();
      const total = pulseTotal?.textContent?.trim();
      const basketContext = items && total ? ` Cart: ${items} items, ${total}.` : '';
      announce(basketLive, `${message}.${basketContext}`);
    }, 140);
  }

  function announceSearchChange() {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      const message = `${resultsCount?.textContent || ''}. ${resultsContext?.textContent || ''}`.trim();
      if (!message || message === lastSearchAnnouncement) return;
      lastSearchAnnouncement = message;
      announce(searchLive, message);
    }, 450);
  }

  function scopeIdFor(target) {
    const scope = target.closest('#product-list, #buy-again-list, #shopping-list, #cart-list');
    return scope?.id || null;
  }

  function restoreActionFocus(scopeId, productId, action) {
    const scope = scopeId ? document.querySelector(`#${scopeId}`) : document;
    if (!scope) return;

    let nextAction = action;
    if (action === 'add') nextAction = 'increment';

    let replacement = scope.querySelector(`[data-product="${productId}"][data-action="${nextAction}"]`);
    if (!replacement && action === 'decrement') {
      replacement = scope.querySelector(`[data-product="${productId}"][data-action="add"]`);
    }
    if (!replacement && action === 'save') {
      replacement = scope.querySelector(`[data-product="${productId}"][data-action="save"]`);
    }

    if (replacement) {
      replacement.focus({ preventScroll: true });
      ensureFocusVisible(replacement);
      return;
    }

    if (scopeId === 'shopping-list' && listPanel && !listPanel.hidden) {
      listPanel.focus({ preventScroll: true });
      ensureFocusVisible(listPanel);
    }
  }

  function ensureFocusVisible(target) {
    if (!target || dock?.contains(target) || pulse?.contains(target)) return;

    window.requestAnimationFrame(() => {
      const rect = target.getBoundingClientRect();
      const header = document.querySelector('.prototype-header');
      const topReserved = (header?.getBoundingClientRect().bottom || 0) + 12;
      const dockHeight = dock?.getBoundingClientRect().height || 0;
      const pulseHeight = pulse && !pulse.hidden ? pulse.getBoundingClientRect().height + 16 : 0;
      const bottomReserved = dockHeight + pulseHeight + 16;
      const bottomLimit = window.innerHeight - bottomReserved;

      if (rect.top < topReserved) {
        window.scrollBy({ top: rect.top - topReserved - 12, behavior: 'auto' });
      } else if (rect.bottom > bottomLimit) {
        window.scrollBy({ top: rect.bottom - bottomLimit + 12, behavior: 'auto' });
      }
    });
  }

  document.addEventListener('click', (event) => {
    const actionTarget = event.target.closest('[data-action][data-product]');
    if (actionTarget) {
      const scopeId = scopeIdFor(actionTarget);
      const productId = actionTarget.dataset.product;
      const action = actionTarget.dataset.action;
      window.setTimeout(() => restoreActionFocus(scopeId, productId, action), 0);
    }

    const aisleButton = event.target.closest('[data-aisle]');
    if (aisleButton) {
      const aisle = aisleButton.dataset.aisle;
      window.setTimeout(() => {
        const replacement = rail.querySelector(`[data-aisle="${aisle}"]`);
        if (replacement) replacement.focus({ preventScroll: true });
      }, 0);
    }

    const navButton = event.target.closest('[data-nav="list"], [data-nav="cart"], #pulse-view-cart');
    if (navButton) {
      lastSurfaceLauncher = navButton;
      const opensCart = navButton.id === 'pulse-view-cart' || navButton.dataset.nav === 'cart';
      window.setTimeout(() => {
        const panel = opensCart ? cartPanel : listPanel;
        if (panel && !panel.hidden) {
          panel.focus({ preventScroll: true });
          ensureFocusVisible(panel);
        }
      }, 0);
    }

    if (event.target.closest('#close-list, #close-cart')) {
      const launcher = lastSurfaceLauncher;
      window.setTimeout(() => {
        if (launcher && document.contains(launcher)) launcher.focus({ preventScroll: true });
      }, 0);
    }
  }, true);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && (!listPanel?.hidden || !cartPanel?.hidden)) {
      const launcher = lastSurfaceLauncher;
      window.setTimeout(() => {
        if (launcher && document.contains(launcher)) launcher.focus({ preventScroll: true });
      }, 0);
    }
  }, true);

  document.addEventListener('focusin', (event) => {
    ensureFocusVisible(event.target);
  });

  const shellObserver = new MutationObserver(() => {
    normalizeAll();
    announceSearchChange();
  });
  shellObserver.observe(shell, { childList: true, subtree: true });

  if (pulseMessage) {
    const pulseObserver = new MutationObserver(announceBasketChange);
    pulseObserver.observe(pulseMessage, { childList: true, characterData: true, subtree: true });
  }

  if (resultsCount && resultsContext) {
    const searchObserver = new MutationObserver(announceSearchChange);
    searchObserver.observe(resultsCount, { childList: true, characterData: true, subtree: true });
    searchObserver.observe(resultsContext, { childList: true, characterData: true, subtree: true });
  }

  normalizeAll();
})();
