const prices = {
            'S': { current: 35000, original: 50000 },
            'M': { current: 45000, original: 65000 },
            'L': { current: 55000, original: 75000 }
        };

        let currentSize = 'M';

        // Format currency
        function formatCurrency(amount) {
            return amount.toLocaleString('vi-VN') + '₫';
        }

        // Update price display
        function updatePrice(size) {
            const priceData = prices[size];
            document.querySelector('.current-price').textContent = formatCurrency(priceData.current);
            document.querySelector('.original-price').textContent = formatCurrency(priceData.original);
            
            // Calculate discount percentage
            const discount = Math.round((1 - priceData.current / priceData.original) * 100);
            document.querySelector('.discount-badge').textContent = `-${discount}%`;
        }

        // Image gallery
        const images = [
            'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600',
            'https://images.unsplash.com/photo-1608198399988-841b2d9e515b?w=600',
            'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600',
            'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600'
        ];

        const mainImage = document.getElementById('mainImage');
        const thumbnails = document.querySelectorAll('.thumbnail');

        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                mainImage.src = images[index];
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });

        // Quantity controls
        let quantity = 1;
        const quantityValue = document.getElementById('quantityValue');
        const decreaseBtn = document.getElementById('decreaseBtn');
        const increaseBtn = document.getElementById('increaseBtn');

        decreaseBtn.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                quantityValue.textContent = quantity;
            }
        });

        increaseBtn.addEventListener('click', () => {
            quantity++;
            quantityValue.textContent = quantity;
        });

        // Size selector
        const sizeButtons = document.querySelectorAll('.size-btn');
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const size = btn.dataset.size;
                currentSize = size;
                
                sizeButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Update price when size changes
                updatePrice(size);
            });
        });

        // Favorite button
        const favoriteBtn = document.getElementById('favoriteBtn');
        favoriteBtn.addEventListener('click', () => {
            favoriteBtn.classList.toggle('active');
        });

        // Tab switching (simplified for single tab)
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.dataset.tab;
                
                tabButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === tabName) {
                        content.classList.add('active');
                    }
                });
            });
        });

        // Add to cart
        const addToCartBtn = document.querySelector('.add-to-cart-btn');
        addToCartBtn.addEventListener('click', () => {
            const totalPrice = prices[currentSize].current * quantity;
            alert(`Đã thêm ${quantity} sản phẩm (Size ${currentSize}) vào giỏ hàng!\nTổng tiền: ${formatCurrency(totalPrice)}`);
        });