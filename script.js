$(document).ready(function () {

    // ================= CART COUNT =================
    function loadCartCount() {
        $.ajax({
            url: 'get_cart_count.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.count !== undefined) {
                    $('#cartCount').text(response.count);
                }
            },
            error: function () {
                console.log("Cart count load failed");
            }
        });
    }

    loadCartCount();

    // ================= TOAST =================
    function showToast(message, type = 'success') {
        const toast = $('#toast');

        toast.removeClass('success error show');
        toast.addClass(type);
        toast.text(message);
        toast.addClass('show');

        setTimeout(() => {
            toast.removeClass('show');
        }, 3000);
    }

    // ================= ADD TO CART =================
  // ==================== PRODUCT MODAL (ADD TO CART & BUY NOW) ====================
let currentProductId = null;

// Open modal when Add to Cart is clicked
$(document).on('click', '.add-to-cart', function(e) {
    e.preventDefault();
    const productCard = $(this).closest('.product-card');
    const productId = productCard.data('id');
    const productName = productCard.find('.product-info h3').text();
    const productPrice = productCard.find('.price .current').text();
    const productOldPrice = productCard.find('.price .old').text();
    let productImage = productCard.find('.product-image img').attr('src');
    
    // Ensure image path is absolute if needed
    if (productImage && !productImage.startsWith('http') && !productImage.startsWith('/')) {
        productImage = '/' + productImage; // adjust if necessary
    }
    
    $('#modalProductName').text(productName);
    $('#modalProductCurrentPrice').text(productPrice);
    $('#modalProductOldPrice').text(productOldPrice);
    $('#modalProductImage').attr('src', productImage);
    $('#modalSize').val('M');
    $('#modalQuantity').val(1);
    currentProductId = productId;
    
    $('#productModal').fadeIn(300);
});

// Close modal
$('.close-modal-product, .product-modal').on('click', function(e) {
    if (e.target === this) {
        $('#productModal').fadeOut(300);
    }
});

// Add to Cart from modal
$('#modalAddToCart').on('click', function() {
    if (!isUserLoggedIn()) {
        window.location.href = 'login.php';
        return;
    }
    const size = $('#modalSize').val();
    const quantity = $('#modalQuantity').val();
    addToCart(currentProductId, size, quantity, false);
});

// Buy Now from modal
$('#modalBuyNow').on('click', function() {
    if (!isUserLoggedIn()) {
        window.location.href = 'login.php';
        return;
    }
    const size = $('#modalSize').val();
    const quantity = $('#modalQuantity').val();
    addToCart(currentProductId, size, quantity, true);
});

function addToCart(productId, size, quantity, redirectToCheckout = false) {
    $.ajax({
        url: 'add_cart.php',
        method: 'POST',
        data: {
            product_id: productId,
            size: size,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('Added to cart!');
                loadCartCount();
                $('#productModal').fadeOut(300);
                if (redirectToCheckout) {
                    window.location.href = 'cart.php';
                }
            } else if (response.redirect) {
                showToast('Please login first', 'error');
                window.location.href = 'login.php';
            } else {
                showToast(response.message || 'Error adding to cart', 'error');
            }
        },
        error: function() {
            showToast('Server error', 'error');
        }
    });
}

// Check login status via AJAX (using your existing check_session.php)
function isUserLoggedIn() {
    let loggedIn = false;
    $.ajax({
        url: 'check_session.php',
        method: 'GET',
        async: false,
        success: function(res) {
            loggedIn = res.logged_in;
        }
    });
    return loggedIn;
}

    // ================= SEARCH =================
    $('#searchBtn').on('click', function () {
        $('#searchBar').toggleClass('active');
    });

    $('#searchSubmit').on('click', function () {
        const query = $('#searchInput').val();
        if (query.trim() !== '') {
            window.location.href = 'search.php?q=' + encodeURIComponent(query);
        }
    });

    $('#searchInput').on('keypress', function (e) {
        if (e.which === 13) {
            $('#searchSubmit').click();
        }
    });

    // ================= MOBILE MENU =================
    $('#mobileMenuBtn').on('click', function () {
        $('.nav-links').toggleClass('active');
    });

    // ================= NEWSLETTER =================
    $('#newsletterForm').on('submit', function (e) {
        e.preventDefault();
        showToast('Subscribed successfully!');
        this.reset();
    });

    // ================= CART QUANTITY =================
    $(document).on('click', '.qty-plus, .qty-minus', function () {

        let input = $(this).siblings('.qty-input');
        let value = parseInt(input.val());

        if (isNaN(value)) value = 1;

        if ($(this).hasClass('qty-plus')) {
            value++;
        } else {
            value = value > 1 ? value - 1 : 1;
        }

        input.val(value);

        const cartId = $(this).closest('.cart-item').data('id');

        $.ajax({
            url: 'update_cart.php',
            method: 'POST',
            data: {
                cart_id: cartId,
                quantity: value
            },
            success: function () {
                location.reload();
            }
        });
    });

    // ================= REMOVE CART =================
    $(document).on('click', '.remove-item', function () {

        const cartId = $(this).data('id');

        if (confirm('Remove this item?')) {

            $.ajax({
                url: 'remove_cart.php',
                method: 'POST',
                data: { cart_id: cartId },
                success: function () {
                    showToast('Item removed');
                    location.reload();
                }
            });
        }
    });

    // ================= CHECKOUT =================
    $('#checkoutBtn').on('click', function () {

        const address = $('#shippingAddress').val();
        const phone = $('#phone').val();

        if (!address || !phone) {
            showToast('Please fill all fields', 'error');
            return;
        }

        $.ajax({
            url: 'checkout.php',
            method: 'POST',
            dataType: 'json',
            data: {
                address: address,
                phone: phone
            },
            success: function (response) {

                if (response.success) {
                    showToast('Order placed! #' + response.order_number);

                    setTimeout(() => {
                        window.location.href = 'orders.php';
                    }, 2000);

                } else {
                    showToast(response.message || 'Checkout failed', 'error');
                }
            },
            error: function () {
                showToast('Something went wrong', 'error');
            }
        });
    });

});
// Magnifying glass for modal image only
$(document).ready(function() {
    var modalContainer = $('#cartModal .modal-product-image');
    if (modalContainer.length === 0) return;
    
    var lens = $('<div class="modal-lens"></div>');
    var result = $('<div class="modal-zoom-result"></div>');
    $('body').append(lens).append(result);
    
    modalContainer.on('mouseenter', function() {
        var img = $(this).find('img');
        if (img.length === 0) return;
        lens.show();
        result.show();
        result.html('<img src="' + img.attr('src') + '">');
        moveLens();
    }).on('mouseleave', function() {
        lens.hide();
        result.hide();
    }).on('mousemove', function(e) {
        moveLens(e);
    });
    
    function moveLens(e) {
        var img = modalContainer.find('img');
        var pos = getCursorPos(e);
        var lensW = lens.outerWidth();
        var lensH = lens.outerHeight();
        var imgW = img.width();
        var imgH = img.height();
        
        var left = pos.x - lensW/2;
        var top = pos.y - lensH/2;
        if (left < 0) left = 0;
        if (top < 0) top = 0;
        if (left > imgW - lensW) left = imgW - lensW;
        if (top > imgH - lensH) top = imgH - lensH;
        
        lens.css({ left: left, top: top });
        
        var zoomResult = $('.modal-zoom-result');
        var ratioX = zoomResult.width() / lensW;
        var ratioY = zoomResult.height() / lensH;
        zoomResult.find('img').css({
            width: imgW * ratioX,
            height: imgH * ratioY,
            left: -left * ratioX,
            top: -top * ratioY
        });
    }
    
    function getCursorPos(e) {
        var rect = modalContainer[0].getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }
});