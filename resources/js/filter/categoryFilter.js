window.filterCategory = function (categoryId, element) {
  // 1. Update UI States (Tabs)
  document.querySelectorAll('.category-tab').forEach(btn => {
    btn.classList.remove('bg-primary', 'text-white', 'shadow-md', 'border-primary');
    btn.classList.add('bg-gray-50', 'text-primary', 'border-transparent');

    if (btn.dataset.category == categoryId) {
      btn.classList.add('bg-primary', 'text-white', 'shadow-md', 'border-primary');
      btn.classList.remove('bg-gray-50', 'text-primary', 'border-transparent');
    }
  });

  // Sync mobile select if needed
  const mobileSelect = document.querySelector('select[onchange*="filterCategory"]');
  if (mobileSelect && element !== mobileSelect) {
    mobileSelect.value = categoryId;
  }

  // 2. Filter Grid Logic
  const items = document.querySelectorAll('.product-item-wrapper');

  items.forEach(item => {
    const isMatch = (categoryId === 'all' || item.dataset.category == categoryId);

    if (isMatch) {
      // Entry Animation
      item.classList.remove('hidden');
      // Browser needs a tiny gap to recognize 'hidden' is gone before animating opacity
      requestAnimationFrame(() => {
        setTimeout(() => {
          item.classList.remove('opacity-0', 'scale-95');
          item.classList.add('opacity-100', 'scale-100');
        }, 10);
      });
    } else {
      // Exit Animation
      item.classList.remove('opacity-100', 'scale-100');
      item.classList.add('opacity-0', 'scale-95');

      // Wait for 500ms (duration-500) before adding 'hidden'
      setTimeout(() => {
        if (item.classList.contains('opacity-0')) {
          item.classList.add('hidden');
        }
      }, 500);
    }
  });
};

// Initial Load Trigger
document.addEventListener('DOMContentLoaded', () => {
  const allBtn = document.querySelector('.category-tab[data-category="all"]');
  if (allBtn) {
    window.filterCategory('all', allBtn);
  }
});