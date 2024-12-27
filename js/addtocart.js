$(() => {
    $('.add-to-cart').on('click', function () {
        const productId = $(this).data('product-id'); // Get product ID from data attribute
        const $quantity = $(`.count[data-product-id="${productId}"]`); // Find the count element

    
        let qty = parseInt($quantity.text());
        $.ajax({
            url: 'addToCart.php',
            type: 'POST',
            data: { productId: productId, qty: qty },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);
                } else {
                    console.log(`Quantity updated`);
                }
            },
            error: function () {
                alert('Failed to update quantity');
            }
        });
        alert("Product add to cart successfully!");
    });
    
});