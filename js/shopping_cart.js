$(() => {

    $('.checkout-btn').on('click', function (e) {
        if ($('.item-checkbox:checked').length === 0) {
            alert('Please select at least one item to proceed to checkout.');
            e.preventDefault();
        }
    });
    
    const $selectAll = $('#select-all-top'); // Top select-all checkbox
    const $selectAllFooter = $('#select-all-footer'); // Footer select-all checkbox
    const $itemCheckboxes = $('.item-checkbox'); // Individual item checkboxes
    const $deleteAllBtn = $('.delete-all-btn');
    const $totalItem = $('.totalitem');
    const $totalAmount = $('.totalamount'); // Total amount display

    // Initial calculation for pre-checked items (if any)
    calculateTotal();

    // Function to calculate selected items and total price
    function calculateTotal() {
        let totalItems = 0;
        let totalPrice = 0;

        $itemCheckboxes.each(function () {
            if ($(this).prop('checked')) {
                const quantity = parseInt($(this).data('quantity'), 10);
                const unitPrice = parseFloat($(this).data('unit-price'));
                totalItems++;
                totalPrice += quantity * unitPrice;
            }
        });

        // Update the total items and total price display
        $deleteAllBtn.text(`Delete (${totalItems})`);
        $totalItem.text(`Total (${totalItems} Item): RM`);
        $totalAmount.text(`${totalPrice.toFixed(2)}`);
    }

    // Handle "Select All" functionality
    $selectAll.add($selectAllFooter).on('change', function () {
        const isChecked = $(this).prop('checked');
        $itemCheckboxes.prop('checked', isChecked);
        $selectAll.prop('checked', isChecked);
        $selectAllFooter.prop('checked', isChecked);
        calculateTotal();
    });

    // Handle individual checkbox selection
    $itemCheckboxes.on('change', function () {
        const allChecked = $itemCheckboxes.length === $('.item-checkbox:checked').length;
        $selectAll.prop('checked', allChecked);
        $selectAllFooter.prop('checked', allChecked);
        calculateTotal();
    });


    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });


    $('.quantity-display').each(function () {
        const cartItemId = $(this).data('cartitem-id');
        const quantity = parseInt($(this).text());
        const $decrementBtn = $(`.decrement-btn[data-cartitem-id="${cartItemId}"]`);

        if (quantity === 1) {
            $decrementBtn.prop('disabled', true);
        }
    });

    $(document).on('click', '.increment-btn', function () {
        const cartItemId = $(this).data('cartitem-id');
        const $quantityDisplay = $(`.quantity-display[data-cartitem-id="${cartItemId}"]`);
        const $decrementBtn = $(`.decrement-btn[data-cartitem-id="${cartItemId}"]`);

        // Increment the quantity
        let quantity = parseInt($quantityDisplay.text());
        quantity++;
        $quantityDisplay.text(quantity);

        // Enable decrement button
        $decrementBtn.prop('disabled', false);

    });

    // Handle decrement button click
    $(document).on('click', '.decrement-btn', function () {
        const cartItemId = $(this).data('cartitem-id');
        const $quantityDisplay = $(`.quantity-display[data-cartitem-id="${cartItemId}"]`);
        const $decrementBtn = $(this);

        // Decrement the quantity
        let quantity = parseInt($quantityDisplay.text());
        if (quantity > 1) {
            quantity--;
            $quantityDisplay.text(quantity);

            // Disable decrement button if quantity is 1
            if (quantity === 1) {
                $decrementBtn.prop('disabled', true);
            }
        }
    });

    // $(document).ready(function () {
    //     // Attach click event to increase and decrease buttons
    //     $('.increase-button, .decrease-button').click(function () {
    //         const button = $(this);
    //         const cartItemId = button.closest('.item').data('cartitem-id');
    //         const action = button.hasClass('increase-button') ? 'add' : 'minus';

    //         // Send AJAX request
    //         $.ajax({
    //             url: 'update_cart.php',
    //             type: 'POST',
    //             data: {
    //                 cartItemId: cartItemId,
    //                 action: action
    //             },
    //             success: function (response) {
    //                 const data = JSON.parse(response);
    //                 if (data.error) {
    //                     alert(data.error);
    //                     return;
    //                 }

    //                 // Update the quantity display
    //                 const quantityDisplay = $(`#quantity-display[data-cartitem-id="${cartItemId}"]`);
    //                 quantityDisplay.text(data.quantity);

    //                 // Update the total price
    //                 const totalPriceCell = quantityDisplay.closest('tr').find('.item_details:last');
    //                 totalPriceCell.text(data.totalPrice);
    //             },
    //             error: function () {
    //                 alert('An error occurred while updating the cart.');
    //             }
    //         });
    //     });
    // });
});