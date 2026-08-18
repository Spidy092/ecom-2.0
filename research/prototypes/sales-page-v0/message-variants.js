(() => {
  const variants = {
    A: {
      label: 'Grocery-first WooCommerce product',
      headline: 'Build the grocery store people can shop quickly.',
      lede: 'A block-first WooCommerce theme + companion core plugin focused on fast basket building, repeat purchases, delivery certainty, and a smaller required stack.',
    },
    B: {
      label: 'A smaller stack for grocery WooCommerce',
      headline: 'Launch a serious grocery store without assembling a plugin pile.',
      lede: 'One focused WooCommerce theme + one companion core plugin for grocery shopping, repeat purchases, delivery certainty, and a guided store setup.',
    },
    C: {
      label: 'WooCommerce designed for the weekly basket',
      headline: 'Make next week’s grocery order easier than this week’s.',
      lede: 'A grocery-first WooCommerce theme + core plugin built around rapid multi-item shopping, Buy Again, household lists, delivery certainty, and mobile basket continuity.',
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
