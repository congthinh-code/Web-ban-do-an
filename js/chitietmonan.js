document.addEventListener('DOMContentLoaded', function () {
  // Thumbnail -> main image switching
  const mainImage = document.getElementById('mainImage');
  const thumbnails = document.querySelectorAll('.thumbnails .thumbnail');

  if (mainImage && thumbnails.length) {
    thumbnails.forEach((thumb) => {
      thumb.addEventListener('click', function () {
        const img = this.querySelector('img');
        if (!img) return;
        // update main image src and active state
        mainImage.src = img.src;
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });
  }

  // Quantity controls
  const decreaseBtn = document.getElementById('decreaseBtn');
  const increaseBtn = document.getElementById('increaseBtn');
  const quantityValue = document.getElementById('quantityValue');

  let qty = quantityValue ? parseInt(quantityValue.textContent, 10) || 1 : 1;

  if (decreaseBtn && increaseBtn && quantityValue) {
    decreaseBtn.addEventListener('click', function () {
      if (qty > 1) {
        qty = qty - 1;
        quantityValue.textContent = qty;
      }
    });

    increaseBtn.addEventListener('click', function () {
      qty = qty + 1;
      quantityValue.textContent = qty;
    });
  }

  // Favorite / like toggle
  const favoriteBtn = document.getElementById('favoriteBtn');
  if (favoriteBtn) {
    favoriteBtn.addEventListener('click', function () {
      this.classList.toggle('active');
      // simple visual change: switch heart icon
      if (this.classList.contains('active')) {
        this.textContent = '♥';
      } else {
        this.textContent = '♡';
      }
    });
  }

  // Simple tab behavior (if tab buttons exist)
  const tabButtons = document.querySelectorAll('.tab-btn');
  if (tabButtons.length) {
    tabButtons.forEach(btn => {
      btn.addEventListener('click', function () {
        const tab = this.dataset.tab;
        if (!tab) return;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
        const target = document.getElementById(tab);
        if (target) target.classList.add('active');
      });
    });
  }
});
