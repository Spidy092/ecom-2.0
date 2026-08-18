(() => {
  'use strict';

  const products = [
    { id: 'milk-amul', name: 'Amul Taaza Milk', unit: '1 L', price: 68, aisle: 'Dairy', icon: '🥛', stock: 'in', repeat: true, saved: true },
    { id: 'curd-nandini', name: 'Nandini Curd', unit: '500 g', price: 45, aisle: 'Dairy', icon: '🥣', stock: 'in', repeat: false, saved: false },
    { id: 'eggs-farm', name: 'Farm Eggs', unit: '6 pcs', price: 78, aisle: 'Dairy', icon: '🥚', stock: 'low', repeat: true, saved: true },
    { id: 'bread-wheat', name: 'Whole Wheat Bread', unit: '400 g', price: 52, aisle: 'Bakery', icon: '🍞', stock: 'in', repeat: true, saved: true },
    { id: 'rice-sona', name: 'Sona Masoori Rice', unit: '5 kg', price: 399, aisle: 'Staples', icon: '🍚', stock: 'in', repeat: true, saved: true },
    { id: 'dal-toor', name: 'Toor Dal', unit: '1 kg', price: 168, aisle: 'Staples', icon: '🫘', stock: 'in', repeat: false, saved: true },
    { id: 'oil-sunflower', name: 'Fortune Sunflower Oil', unit: '1 L', price: 145, aisle: 'Staples', icon: '🫗', stock: 'low', repeat: true, saved: false },
    { id: 'tomato', name: 'Fresh Tomato', unit: '1 kg', price: 42, aisle: 'Produce', icon: '🍅', stock: 'in', repeat: true, saved: true },
    { id: 'onion', name: 'Red Onion', unit: '1 kg', price: 38, aisle: 'Produce', icon: '🧅', stock: 'in', repeat: false, saved: false },
    { id: 'banana', name: 'Banana Robusta', unit: '6 pcs', price: 55, aisle: 'Produce', icon: '🍌', stock: 'in', repeat: true, saved: true },
    { id: 'apple', name: 'Royal Gala Apple', unit: '4 pcs', price: 180, aisle: 'Produce', icon: '🍎', stock: 'in', repeat: false, saved: false },
    { id: 'chips-bingo', name: 'Bingo Potato Chips', unit: '90 g', price: 30, aisle: 'Snacks', icon: '🥔', stock: 'in', repeat: false, saved: false },
    { id: 'surf', name: 'Surf Excel Matic', unit: '1 kg', price: 245, aisle: 'Household', icon: '🧺', stock: 'in', repeat: false, saved: true },
    { id: 'toothpaste', name: 'Colgate Strong Teeth', unit: '200 g', price: 110, aisle: 'Household', icon: '🪥', stock: 'in', repeat: false, saved: false }
  ];

  const aisleMeta = {
    Produce: { code: '01', description: 'Fresh fruit and vegetables with pack/unit information up front.' },
    Dairy: { code: '02', description: 'Milk, eggs and chilled staples optimized for frequent repeat purchases.' },
    Bakery: { code: '03', description: 'Everyday bakery items without forcing product-detail navigation.' },
    Staples: { code: '04', description: 'Rice, dal and cooking essentials with scan-friendly unit and price.' },
    Snacks: { code: '05', description: 'Fast add for small impulse products without quick-view clutter.' },
    Household: { code: '06', description: 'Non-food household essentials in the same basket-building flow.' }
  };

  const state = {
    mode: 'first-time',
    aisle: 'All',
    query: '',
    cart: new Map(),
    saved: new Set(products.filter((p) => p.saved).map((p) => p.id)),
    research: {
      startedAt: Date.now(),
      interactions: 0,
      surfaces: 1,
      firstAddAt: null,
      timer: null
    }
  };

  const els = {
    shell: document.querySelector('.prototype-shell'),
    modeButtons: [...document.querySelectorAll('[data-mode-button]')],
    returningPanel: document.querySelector('#returning-panel'),
    buyAgainList: document.querySelector('#buy-again-list'),
    deliveryForm: document.querySelector('#delivery-form'),
    postcode: document.querySelector('#postcode'),
    deliveryResult: document.querySelector('#delivery-result'),
    search: document.querySelector('#search-input'),
    aisleRail: document.querySelector('#aisle-rail'),
    aisleMarker: document.querySelector('#aisle-marker'),
    aisleName: document.querySelector('#aisle-name'),
    aisleDescription: document.querySelector('#aisle-description'),
    productList: document.querySelector('#product-list'),
    resultsCount: document.querySelector('#results-count'),
    resultsContext: document.querySelector('#results-context'),
    emptyState: document.querySelector('#empty-state'),
    listPanel: document.querySelector('#list-panel'),
    shoppingList: document.querySelector('#shopping-list'),
    closeList: document.querySelector('#close-list'),
    cartPanel: document.querySelector('#cart-panel'),
    cartList: document.querySelector('#cart-list'),
    cartSummaryItems: document.querySelector('#cart-summary-items'),
    cartSummaryTotal: document.querySelector('#cart-summary-total'),
    closeCart: document.querySelector('#close-cart'),
    showAll: document.querySelector('#show-all'),
    pulse: document.querySelector('#basket-pulse'),
    pulseMessage: document.querySelector('#pulse-message'),
    pulseItems: document.querySelector('#pulse-items'),
    pulseTotal: document.querySelector('#pulse-total'),
    pulseViewCart: document.querySelector('#pulse-view-cart'),
    dockCartCount: document.querySelector('#dock-cart-count'),
    navButtons: [...document.querySelectorAll('[data-nav]')],
    resetResearch: document.querySelector('#reset-research'),
    metricTime: document.querySelector('#metric-time'),
    metricInteractions: document.querySelector('#metric-interactions'),
    metricSurfaces: document.querySelector('#metric-surfaces'),
    metricFirstAdd: document.querySelector('#metric-first-add'),
    prototypeCheckout: document.querySelector('#prototype-checkout')
  };

  function money(value) {
    return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(value);
  }

  function cartQuantity(productId) {
    return state.cart.get(productId) || 0;
  }

  function cartStats() {
    let itemCount = 0;
    let total = 0;
    for (const [id, qty] of state.cart.entries()) {
      const product = products.find((p) => p.id === id);
      if (!product) continue;
      itemCount += qty;
      total += qty * product.price;
    }
    return { itemCount, total };
  }

  function trackInteraction(type = 'interaction') {
    state.research.interactions += 1;
    if (type === 'surface') state.research.surfaces += 1;
    renderMetrics();
  }

  function secondsSinceStart() {
    return Math.max(0, Math.round((Date.now() - state.research.startedAt) / 1000));
  }

  function formatDuration(seconds) {
    const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
    const secs = (seconds % 60).toString().padStart(2, '0');
    return `${minutes}:${secs}`;
  }

  function renderMetrics() {
    els.metricTime.textContent = formatDuration(secondsSinceStart());
    els.metricInteractions.textContent = String(state.research.interactions);
    els.metricSurfaces.textContent = String(state.research.surfaces);
    els.metricFirstAdd.textContent = state.research.firstAddAt == null ? '—' : `${state.research.firstAddAt}s`;
  }

  function resetResearch() {
    state.research.startedAt = Date.now();
    state.research.interactions = 0;
    state.research.surfaces = 1;
    state.research.firstAddAt = null;
    state.cart.clear();
    state.query = '';
    state.aisle = 'All';
    els.search.value = '';
    renderAll();
    renderMetrics();
    announcePulse('Research run reset');
  }

  function setMode(mode) {
    if (state.mode === mode) return;
    state.mode = mode;
    els.shell.dataset.mode = mode;
    els.modeButtons.forEach((button) => {
      const active = button.dataset.modeButton === mode;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    els.returningPanel.hidden = mode !== 'returning';
    trackInteraction('surface');
    if (mode === 'returning') renderBuyAgain();
  }

  function renderAisles() {
    const aisles = ['All', ...Object.keys(aisleMeta)];
    els.aisleRail.innerHTML = aisles.map((aisle) => {
      const selected = state.aisle === aisle;
      const code = aisle === 'All' ? '00' : aisleMeta[aisle].code;
      return `<button type="button" class="aisle-button" role="tab" aria-selected="${selected}" data-aisle="${aisle}"><span class="aisle-code">${code}</span>${aisle}</button>`;
    }).join('');
  }

  function renderAisleSummary() {
    if (state.aisle === 'All') {
      els.aisleMarker.textContent = 'ALL';
      els.aisleName.textContent = 'All groceries';
      els.aisleDescription.textContent = 'A compact product ledger optimized for multi-item shopping.';
      return;
    }
    const meta = aisleMeta[state.aisle];
    els.aisleMarker.textContent = meta.code;
    els.aisleName.textContent = state.aisle;
    els.aisleDescription.textContent = meta.description;
  }

  function filteredProducts() {
    const query = state.query.trim().toLowerCase();
    return products.filter((product) => {
      const aisleMatch = state.aisle === 'All' || product.aisle === state.aisle;
      const queryMatch = !query || `${product.name} ${product.unit} ${product.aisle}`.toLowerCase().includes(query);
      return aisleMatch && queryMatch;
    });
  }

  function quantityMarkup(product) {
    const qty = cartQuantity(product.id);
    if (qty <= 0) {
      return `<div class="quick-actions"><button type="button" class="add-button" data-action="add" data-product="${product.id}" aria-label="Add ${product.name} ${product.unit} to basket">Add</button><button type="button" class="save-button ${state.saved.has(product.id) ? 'is-saved' : ''}" data-action="save" data-product="${product.id}" aria-pressed="${state.saved.has(product.id)}">${state.saved.has(product.id) ? 'Saved' : 'Save to list'}</button></div>`;
    }
    return `<div class="quick-actions"><div class="quantity-control" aria-label="Quantity for ${product.name}"><button type="button" class="qty-button" data-action="decrement" data-product="${product.id}" aria-label="Decrease ${product.name} quantity">−</button><span class="qty-value" aria-live="polite">${qty}</span><button type="button" class="qty-button" data-action="increment" data-product="${product.id}" aria-label="Increase ${product.name} quantity">+</button></div><button type="button" class="save-button ${state.saved.has(product.id) ? 'is-saved' : ''}" data-action="save" data-product="${product.id}" aria-pressed="${state.saved.has(product.id)}">${state.saved.has(product.id) ? 'Saved' : 'Save to list'}</button></div>`;
  }

  function renderProducts() {
    const visible = filteredProducts();
    els.resultsCount.textContent = `${visible.length} product${visible.length === 1 ? '' : 's'}`;
    const contexts = [];
    if (state.aisle !== 'All') contexts.push(state.aisle);
    if (state.query.trim()) contexts.push(`Search: “${state.query.trim()}”`);
    els.resultsContext.textContent = contexts.length ? contexts.join(' · ') : 'All aisles';
    els.emptyState.hidden = visible.length !== 0;

    els.productList.innerHTML = visible.map((product) => `
      <article class="product-row" data-product-row="${product.id}">
        <div class="product-thumb" aria-hidden="true">${product.icon}</div>
        <div class="product-primary">
          <strong>${product.name}</strong>
          <small>${product.unit} · ${product.aisle}</small>
        </div>
        <span class="product-stock ${product.stock === 'low' ? 'low' : ''}">${product.stock === 'low' ? 'Low stock' : 'In stock'}</span>
        <span class="product-price">${money(product.price)}</span>
        ${quantityMarkup(product)}
      </article>
    `).join('');
  }

  function renderBuyAgain() {
    const repeat = products.filter((product) => product.repeat).slice(0, 5);
    els.buyAgainList.innerHTML = repeat.map((product) => `
      <article class="quick-item">
        <span aria-hidden="true">${product.icon}</span>
        <strong>${product.name}</strong>
        <small>${product.unit} · ${money(product.price)}</small>
        ${quantityMarkup(product)}
      </article>
    `).join('');
  }

  function renderShoppingList() {
    const saved = products.filter((product) => state.saved.has(product.id));
    els.shoppingList.innerHTML = saved.length ? saved.map((product) => `
      <article class="quick-item">
        <span aria-hidden="true">${product.icon}</span>
        <strong>${product.name}</strong>
        <small>${product.unit} · ${money(product.price)}</small>
        ${quantityMarkup(product)}
      </article>
    `).join('') : '<p class="empty-state">Nothing saved yet. Save a grocery from the product ledger.</p>';
  }

  function renderCart() {
    const entries = [...state.cart.entries()].filter(([, qty]) => qty > 0);
    if (!entries.length) {
      els.cartList.innerHTML = '<p class="empty-state">Your basket is empty. Add groceries from the aisle ledger.</p>';
    } else {
      els.cartList.innerHTML = entries.map(([id, qty]) => {
        const product = products.find((p) => p.id === id);
        return `
          <article class="cart-row">
            <div>
              <strong>${product.name}</strong>
              <small>${product.unit}</small>
            </div>
            <span class="cart-row-price">${money(product.price * qty)}</span>
            <div class="quantity-control" aria-label="Quantity for ${product.name}">
              <button type="button" class="qty-button" data-action="decrement" data-product="${product.id}" aria-label="Decrease ${product.name} quantity">−</button>
              <span class="qty-value" aria-live="polite">${qty}</span>
              <button type="button" class="qty-button" data-action="increment" data-product="${product.id}" aria-label="Increase ${product.name} quantity">+</button>
            </div>
          </article>
        `;
      }).join('');
    }

    const stats = cartStats();
    els.cartSummaryItems.textContent = String(stats.itemCount);
    els.cartSummaryTotal.textContent = money(stats.total);
  }

  function renderBasketState(message = '') {
    const stats = cartStats();
    els.pulseItems.textContent = String(stats.itemCount);
    els.pulseTotal.textContent = money(stats.total);
    els.dockCartCount.textContent = String(stats.itemCount);
    els.dockCartCount.hidden = stats.itemCount === 0;

    if (stats.itemCount === 0) {
      els.pulse.hidden = true;
    } else {
      els.pulse.hidden = false;
      if (message) els.pulseMessage.textContent = message;
    }
  }

  function announcePulse(message) {
    els.pulseMessage.textContent = message;
    renderBasketState(message);
  }

  function changeQuantity(productId, delta) {
    const product = products.find((p) => p.id === productId);
    if (!product) return;
    const current = cartQuantity(productId);
    const next = Math.max(0, current + delta);
    if (next === 0) state.cart.delete(productId);
    else state.cart.set(productId, next);

    if (delta > 0 && state.research.firstAddAt == null) {
      state.research.firstAddAt = secondsSinceStart();
    }

    trackInteraction();
    const message = next === 0 ? `Removed ${product.name}` : `${product.name} ×${next}`;
    renderDynamicSurfaces();
    announcePulse(message);
  }

  function toggleSaved(productId) {
    const product = products.find((p) => p.id === productId);
    if (!product) return;
    if (state.saved.has(productId)) state.saved.delete(productId);
    else state.saved.add(productId);
    trackInteraction();
    renderDynamicSurfaces();
    announcePulse(state.saved.has(productId) ? `Saved ${product.name} to your list` : `Removed ${product.name} from your list`);
  }

  function handleProductAction(target) {
    const action = target.dataset.action;
    const productId = target.dataset.product;
    if (!action || !productId) return false;
    if (action === 'add' || action === 'increment') changeQuantity(productId, 1);
    if (action === 'decrement') changeQuantity(productId, -1);
    if (action === 'save') toggleSaved(productId);
    return true;
  }

  function openSurface(surface) {
    els.listPanel.hidden = surface !== 'list';
    els.cartPanel.hidden = surface !== 'cart';
    if (surface === 'list') {
      renderShoppingList();
      els.listPanel.scrollIntoView({ block: 'start' });
    }
    if (surface === 'cart') {
      renderCart();
      els.cartPanel.scrollIntoView({ block: 'start' });
    }
    trackInteraction('surface');
  }

  function renderDynamicSurfaces() {
    renderProducts();
    if (state.mode === 'returning') renderBuyAgain();
    if (!els.listPanel.hidden) renderShoppingList();
    if (!els.cartPanel.hidden) renderCart();
    renderBasketState();
  }

  function renderAll() {
    renderAisles();
    renderAisleSummary();
    renderProducts();
    renderBuyAgain();
    renderShoppingList();
    renderCart();
    renderBasketState();
  }

  els.modeButtons.forEach((button) => button.addEventListener('click', () => setMode(button.dataset.modeButton)));

  els.deliveryForm.addEventListener('submit', (event) => {
    event.preventDefault();
    trackInteraction();
    const value = els.postcode.value.trim();
    const available = new Set(['560001', '560034', '560038', '560102']).has(value);
    els.deliveryResult.classList.toggle('is-unavailable', !available);
    els.deliveryResult.textContent = available
      ? 'Prototype: delivery available today.'
      : 'Prototype: delivery is not available for this postcode.';
  });

  els.search.addEventListener('input', (event) => {
    state.query = event.target.value;
    renderProducts();
  });

  els.search.addEventListener('search', () => {
    state.query = els.search.value;
    renderProducts();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === '/' && document.activeElement !== els.search && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
      event.preventDefault();
      els.search.focus();
      trackInteraction('surface');
    }
    if (event.key === 'Escape') {
      if (!els.listPanel.hidden || !els.cartPanel.hidden) {
        els.listPanel.hidden = true;
        els.cartPanel.hidden = true;
        trackInteraction('surface');
      }
    }
  });

  els.aisleRail.addEventListener('click', (event) => {
    const button = event.target.closest('[data-aisle]');
    if (!button) return;
    state.aisle = button.dataset.aisle;
    state.query = '';
    els.search.value = '';
    trackInteraction('surface');
    renderAisles();
    renderAisleSummary();
    renderProducts();
  });

  els.showAll.addEventListener('click', () => {
    state.aisle = 'All';
    state.query = '';
    els.search.value = '';
    trackInteraction('surface');
    renderAisles();
    renderAisleSummary();
    renderProducts();
  });

  document.addEventListener('click', (event) => {
    const actionTarget = event.target.closest('[data-action]');
    if (actionTarget) handleProductAction(actionTarget);
  });

  els.closeList.addEventListener('click', () => { els.listPanel.hidden = true; trackInteraction('surface'); });
  els.closeCart.addEventListener('click', () => { els.cartPanel.hidden = true; trackInteraction('surface'); });
  els.pulseViewCart.addEventListener('click', () => openSurface('cart'));

  els.navButtons.forEach((button) => button.addEventListener('click', () => {
    els.navButtons.forEach((item) => item.classList.remove('is-current'));
    button.classList.add('is-current');
    const nav = button.dataset.nav;
    if (nav === 'home') {
      document.querySelector('.delivery-band').scrollIntoView({ block: 'start' });
      trackInteraction('surface');
    }
    if (nav === 'search') {
      document.querySelector('.search-workspace').scrollIntoView({ block: 'start' });
      els.search.focus({ preventScroll: true });
      trackInteraction('surface');
    }
    if (nav === 'aisles') {
      document.querySelector('.aisle-workspace').scrollIntoView({ block: 'start' });
      trackInteraction('surface');
    }
    if (nav === 'list') openSurface('list');
    if (nav === 'cart') openSurface('cart');
  }));

  els.resetResearch.addEventListener('click', resetResearch);
  els.prototypeCheckout.addEventListener('click', () => {
    trackInteraction('surface');
    announcePulse('Prototype boundary reached — WooCommerce owns checkout');
  });

  state.research.timer = window.setInterval(renderMetrics, 1000);
  renderAll();
  renderMetrics();
})();
