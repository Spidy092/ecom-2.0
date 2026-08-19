(() => {
  const variants = {
    A: {
      label: 'Grocery-first WooCommerce for multi-item shopping',
      headline: 'Launch a grocery WooCommerce store built around the weekly basket.',
      lede: 'A block-first theme + companion core plugin for multi-item grocery shopping, early delivery checks, Saved / Buy Again workflows, and a smaller required stack—without mandatory Elementor.',
    },
    B: {
      label: 'A smaller required stack for grocery WooCommerce',
      headline: 'Launch one focused grocery storefront without assembling a plugin pile.',
      lede: 'WordPress + WooCommerce + one theme + one companion core plugin, with one Modern Grocery starter store and a guided setup path as the working V1 package.',
    },
    C: {
      label: 'One focused grocery product',
      headline: 'Start with a grocery storefront designed for shopping, setup, and maintenance.',
      lede: 'The working offer combines a focused starter store, mobile grocery interactions, transparent compatibility, maintained updates, and standard support instead of competing on demo count alone.',
    },
  };

  const requested = new URLSearchParams(window.location.search).get('variant')?.toUpperCase();
  const key = variants[requested] ? requested : 'A';
  const variant = variants[key];

  const label = document.querySelector('.hero-copy .eyebrow');
  const title = document.querySelector('#hero-title');
  const lede = document.querySelector('.hero-copy .lede');
  if (label) label.textContent = variant.label;
  if (title) title.textContent = variant.headline;
  if (lede) lede.textContent = variant.lede;

  document.documentElement.dataset.messageVariant = key;

  // Facilitator-only identifier. This is intentionally small and carries no
  // user/session data; it simply makes screenshots/notes attributable.
  const banner = document.querySelector('.research-banner');
  if (banner) {
    const marker = document.createElement('span');
    marker.className = 'variant-marker';
    marker.textContent = `Message variant ${key}`;
    banner.appendChild(marker);
  }
})();
