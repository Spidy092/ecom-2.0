(() => {
  'use strict';

  const panel = document.querySelector('.research-console-panel');
  const shell = document.querySelector('.prototype-shell');
  const resetButton = document.querySelector('#reset-research');
  if (!panel || !shell || !resetButton) return;

  const recorder = document.createElement('section');
  recorder.className = 'session-recorder';
  recorder.setAttribute('aria-labelledby', 'session-recorder-title');
  recorder.innerHTML = `
    <div class="session-recorder-heading">
      <strong id="session-recorder-title">Session evidence</strong>
      <span>Local only · no telemetry</span>
    </div>
    <div class="session-recorder-fields">
      <label>
        Participant code
        <input id="session-participant-code" type="text" maxlength="24" placeholder="S01" autocomplete="off" />
      </label>
      <label>
        Group
        <select id="session-participant-group">
          <option value="shopper">Shopper</option>
          <option value="builder">WooCommerce builder</option>
          <option value="store-owner">Store owner</option>
          <option value="other">Other</option>
        </select>
      </label>
    </div>
    <div class="session-recorder-actions">
      <button class="quiet-button" type="button" id="export-session">Export anonymous JSON</button>
      <span id="session-recorder-status" role="status" aria-live="polite">No data leaves this browser automatically.</span>
    </div>
  `;
  panel.append(recorder);

  const participantCode = recorder.querySelector('#session-participant-code');
  const participantGroup = recorder.querySelector('#session-participant-group');
  const exportButton = recorder.querySelector('#export-session');
  const status = recorder.querySelector('#session-recorder-status');

  let sessionStartedAt = performance.now();
  let events = [];
  let searchTimer = null;

  const elapsedMs = () => Math.max(0, Math.round(performance.now() - sessionStartedAt));

  function record(type, data = {}) {
    events.push({
      at_ms: elapsedMs(),
      type,
      ...data,
    });
  }

  function sourceOf(element) {
    if (element.closest('#buy-again-list')) return 'buy-again';
    if (element.closest('#shopping-list')) return 'shopping-list';
    if (element.closest('#cart-list')) return 'cart';
    if (element.closest('#product-list')) return 'product-ledger';
    if (element.closest('#aisle-rail')) return 'aisle-rail';
    if (element.closest('.mobile-dock')) return 'mobile-dock';
    return 'storefront';
  }

  document.addEventListener('click', (event) => {
    const target = event.target.closest('button, a, summary');
    if (!target || target.closest('.research-console')) return;

    if (target.dataset.action) {
      record('product_action', {
        action: target.dataset.action,
        product: target.dataset.product || null,
        source: sourceOf(target),
      });
      return;
    }

    if (target.dataset.nav) {
      record('navigation', { destination: target.dataset.nav, source: 'mobile-dock' });
      return;
    }

    if (target.dataset.aisle) {
      record('aisle_change', { aisle: target.dataset.aisle });
      return;
    }

    if (target.id === 'show-all') record('aisle_show_all');
    else if (target.id === 'pulse-view-cart') record('basket_pulse_open_cart');
    else if (target.id === 'close-cart') record('cart_close');
    else if (target.id === 'close-list') record('shopping_list_close');
    else if (target.id === 'prototype-checkout') record('prototype_checkout_boundary');
  }, true);

  const deliveryForm = document.querySelector('#delivery-form');
  deliveryForm?.addEventListener('submit', () => {
    window.setTimeout(() => {
      const text = document.querySelector('#delivery-result')?.textContent || '';
      record('delivery_check', {
        available: /available today/i.test(text),
      });
    }, 0);
  });

  const search = document.querySelector('#search-input');
  search?.addEventListener('input', () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      record('search_change', {
        query_length: search.value.length,
        empty: search.value.length === 0,
      });
    }, 350);
  });

  resetButton.addEventListener('click', () => {
    events = [];
    sessionStartedAt = performance.now();
    status.textContent = 'Session event log reset.';
  });

  function text(selector) {
    return (document.querySelector(selector)?.textContent || '').trim();
  }

  function buildExport() {
    const code = participantCode.value.trim().replace(/[^a-zA-Z0-9_-]/g, '').slice(0, 24) || 'anonymous';
    return {
      schema: 'aisleflow-v0-session-1',
      exported_at: new Date().toISOString(),
      participant: {
        code,
        group: participantGroup.value,
      },
      privacy: {
        network_telemetry: false,
        search_terms_recorded: false,
        postcode_recorded: false,
        note: 'This file is generated locally in the browser. Search text and postcode values are intentionally excluded.',
      },
      prototype: {
        mode: shell.dataset.mode || 'unknown',
        viewport_css_px: {
          width: window.innerWidth,
          height: window.innerHeight,
        },
      },
      built_in_metrics: {
        elapsed: text('#metric-time'),
        deliberate_interactions: text('#metric-interactions'),
        surfaces: text('#metric-surfaces'),
        first_add: text('#metric-first-add'),
      },
      final_state: {
        basket_items: text('#dock-cart-count') || '0',
        basket_total: text('#pulse-total') || '₹0',
        delivery_result: text('#delivery-result'),
      },
      recorder: {
        elapsed_ms: elapsedMs(),
        event_count: events.length,
        events,
      },
    };
  }

  exportButton.addEventListener('click', () => {
    const data = buildExport();
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const safeCode = data.participant.code || 'anonymous';
    link.href = url;
    link.download = `aisleflow-v0-${safeCode}-${Date.now()}.json`;
    document.body.append(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
    status.textContent = `Exported ${data.recorder.event_count} recorded events locally.`;
  });
})();
