$(() => {
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    $('.checkout-btn').on('click', function (e) {
        if ($('.item-checkbox:checked').length === 0) {
            alert('Please select at least one item to proceed to checkout.');
            e.preventDefault();
        }
    });

    const $selectAll = $('#select-all-top');
    const $selectAllFooter = $('#select-all-footer');
    const $itemCheckboxes = $('.item-checkbox');
    const $deleteAllBtn = $('.delete-all-btn');
    const $totalItem = $('.totalitem');
    const $totalAmount = $('.totalamount');

    function calculateTotal() {
        let totalItems = 0;
        let totalPrice = 0;

        $itemCheckboxes.each(function () {
            if ($(this).prop('checked')) {
                const cartItemId = $(this).data('cartitem-id');
                const $quantityDisplay = $(`.quantity-display[data-cartitem-id="${cartItemId}"]`);
                const quantity = parseInt($quantityDisplay.text(), 10);
                const $unitPriceDisplay = $(`.cart-unitPrice[data-cartitem-id="${cartItemId}"]`);
                const unitPrice = parseFloat($unitPriceDisplay.text());
                totalItems++;
                totalPrice += quantity * unitPrice;
            }
        });

        $deleteAllBtn.text(`Delete (${totalItems})`);
        $totalItem.text(`Total (${totalItems} Item): RM`);
        $totalAmount.text(`${totalPrice.toFixed(2)}`);
    }

    $selectAll.add($selectAllFooter).on('change', function () {
        const isChecked = $(this).prop('checked');
        $itemCheckboxes.prop('checked', isChecked);
        $selectAll.prop('checked', isChecked);
        $selectAllFooter.prop('checked', isChecked);
        calculateTotal();
    });

    $itemCheckboxes.on('change', function () {
        const allChecked = $itemCheckboxes.length === $('.item-checkbox:checked').length;
        $selectAll.prop('checked', allChecked);
        $selectAllFooter.prop('checked', allChecked);
        calculateTotal();
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

        let quantity = parseInt($quantityDisplay.text());
        quantity++;
        $quantityDisplay.text(quantity);

        $decrementBtn.prop('disabled', false);

        updateQuantity(cartItemId, quantity);
        calculateSubTotal(cartItemId, quantity);
        calculateTotal();
    });

    $(document).on('click', '.decrement-btn', function () {
        const cartItemId = $(this).data('cartitem-id');
        const $quantityDisplay = $(`.quantity-display[data-cartitem-id="${cartItemId}"]`);
        const $decrementBtn = $(this);

        let quantity = parseInt($quantityDisplay.text());
        if (quantity > 1) {
            quantity--;
            $quantityDisplay.text(quantity);

            if (quantity === 1) {
                $decrementBtn.prop('disabled', true);
            }

            updateQuantity(cartItemId, quantity);
            calculateSubTotal(cartItemId, quantity);
            calculateTotal();
        }
    });

    function updateQuantity(cartItemId, quantity) {
        $.ajax({
            url: 'update_quantity.php',
            type: 'POST',
            data: { cartItemId: cartItemId, quantity: quantity },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);
                } else {
                    console.log(`Quantity updated: ${data.quantity}`);
                }
            },
            error: function () {
                alert('Failed to update quantity.');
            }
        });
    }

    function calculateSubTotal(cartItemId, quantity) {
        const $unitPrice = $(`.cart-unitPrice[data-cartitem-id="${cartItemId}"]`);
        const $totalPrice = $(`.cart-totalPrice[data-cartitem-id="${cartItemId}"]`);
        const subtotal = parseFloat($unitPrice.text()) * quantity;
        $totalPrice.text(`RM ${subtotal.toFixed(2)}`);
    }

    $deleteAllBtn.on('click', function (e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete all selected items from your cart?')) {
            return;
        }

        $itemCheckboxes.each(function () {
            if ($(this).prop('checked')) {
                const cartItemId = $(this).data('cartitem-id');
                deleteSelectedItem(cartItemId);
            }
        });

        setTimeout(() => {
            location.reload();
        }, 1);
    })

    function deleteSelectedItem(cartItemId) {
        $.ajax({
            url: 'cartItem_deleteAll.php',
            type: 'POST',
            data: { cartItemId: cartItemId },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);
                } else {
                    console.log(`Successfully deleted item with ID: ${cartItemId}`);
                    //$(`.cart-item[data-cartitem-id="${cartItemId}"]`).remove();
                    calculateTotal();
                }
            },
            error: function () {
                alert('Failed to delete all item.');
            }
        });
    }

    $('.checkout-addressTable-d-btn').click(function () {
        $('#checkout-addresspopup-overlay, #checkout-addresspopup-modal').fadeIn();
    });

    $('#checkout-addresspopup-cancel-btn').click(function () {
        $('#checkout-addresspopup-overlay, #checkout-addresspopup-modal').fadeOut();
    });

    $('.checkout-addresspopup-edit-btn').click(function () {
        const addressId = $(this).data('address-id');
        const addressLine = $(this).data('address-line');
        const postalCode = $(this).data('postal-code');
        const state = $(this).data('state');

        $('#addressId').val(addressId);
        $('#addressLine').val(addressLine);
        $('#postalCode').val(postalCode);
        $('#state').val(state);

        $('#checkout-editpopup-modal').fadeIn();
        $('#checkout-addresspopup-modal').fadeOut();

    });

    $('#checkout-addresspopup-confirm-btn').click(function () {
        $('.checkout-addresspopup-radio-btn').each(function () {
            if ($(this).prop('checked')) {
                const addressId = $(this).data('address-id');
                const oriaddressId = $(this).data('ori-address-id');
                changeLocation(addressId, oriaddressId);
            }
        });
        $('#popup-overlay, #popup-modal').fadeOut();
        setTimeout(() => {
            location.reload();
        }, 1);
    });

    function changeLocation(addressId, oriaddressId) {
        $.ajax({
            url: 'changeLocation.php',
            type: 'POST',
            data: { addressId: addressId, oriaddressId: oriaddressId },
            success: function (response) {
                const data = JSON.parse(response);
                if (data.error) {
                    alert(data.error);
                } else {
                    console.log(`Successfully Change Location: ${addressId}`);
                }
            },
            error: function () {
                alert('Failed to change location.');
            }
        });
    }

    $('.input-box').on('focus', function () {
        $(this).closest('fieldset').css('border-color', 'black');
    });

    $('.input-box').on('blur', function () {
        $(this).closest('fieldset').css('border-color', '');
    });

    $('#checkout-editpopup-confirm-btn').click(function () {
        const formData = {
            address_id: $('#addressId').val(),
            address_line: $('#addressLine').val(),
            postal_code: $('#postalCode').val(),
            state: $('#state').val(),
        };

        $.ajax({
            url: 'update_address.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#checkout-editpopup-modal').fadeOut();
                $('#checkout-addresspopup-modal').fadeIn();
                location.reload();
            },
            error: function (xhr, status, error) {
                alert('Failed to update address. Please try again.');
            }
        });
    });

    $('#checkout-editpopup-cancel-btn').click(function () {
        $('#checkout-editpopup-modal').fadeOut();
        $('#checkout-addresspopup-modal').fadeIn();
    });

    $('.checkout-addresspopup-addnew-btn').click(function () {
        $('#checkout-addresspopup-modal').fadeOut();
        $('#checkout-addaddress-modal').fadeIn();
    });

    $('#checkout-addaddress-confirm-btn').click(function () {
        const formData = {
            address_line: $('#newAddressLine').val(),
            postal_code: $('#newPostalCode').val(),
            state: $('#newState').val(),
        };

        $.ajax({
            url: 'add_new_address.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                location.reload();
            },
            error: function (xhr, status, error) {
                alert('Failed to add new address. Please try again.');
            }
        });
    });

    $('#checkout-addaddress-cancel-btn').click(function () {
        $('#checkout-addaddress-modal').fadeOut();
        $('#checkout-addresspopup-modal').fadeIn();
    });

    $('.payment-option').on('click', function () {
        $('.payment-option').removeClass('active');
        $(this).addClass('active');
        const selectedMethod = $(this).data('method');
        $('#paymentMethod').val(selectedMethod);
    });

    $('.checkout-placeOrder-btn').on('click', function (e) {
        e.preventDefault();

        const paymentMethod = $('#paymentMethod').val();
        if (paymentMethod === 'Credit / Debit Card') {
            $('#checkout-addresspopup-overlay, #cardDetailsModal').fadeIn();
        } else if (paymentMethod === 'Boots.Pay') {
            $('form').submit();
        }
    });

    $('#closeCardModal').on('click', function () {
        $('#checkout-addresspopup-overlay,#cardDetailsModal').fadeOut();
    });

    $('#pay-btn').on('click', function () {
        const cardNumber = $("#cardNumber").val().trim();
        const cardExpiry = $("#cardExpiry").val().trim();
        const cardCVV = $("#cardCVV").val().trim();
        const cardCountry = $("#cardCountry").val().trim();
        const cardZipCode = $("#cardZipCode").val().trim();

        if (!cardNumber || !cardExpiry || !cardCVV || !cardCountry || !cardZipCode) {
            alert("Please fill in all fields.");
            return;
        }

        const cardNumberRegex = /^\d{4} \d{4} \d{4} \d{4}$/;
        if (!cardNumberRegex.test(cardNumber)) {
            alert('Invalid card number format. Use "xxxx xxxx xxxx xxxx".');
            return;
        }

        const expiryDateRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
        if (!expiryDateRegex.test(cardExpiry)) {
            alert('Invalid expiry date format. Use "MM/YY".');
            return;
        }

        const [month, year] = expiryDate.split('/');
        const currentDate = new Date();
        const expiry = new Date(`20${year}`, month - 1); // Convert MM/YY to full date
        if (expiry <= currentDate) {
            alert('Expiry date must be in the future.');
            return;
        }

        const cvvRegex = /^\d{3}$/;
        if (!cvvRegex.test(cardCVV)) {
            alert('Invalid CVV. Must be exactly 3 digits.');
            return;
        }

        const zipRegex = /^\d{5}$/;
        if (!zipRegex.test(cardZipCode)) {
            alert('Invalid ZIP Code. Must be exactly 5 digits.');
            return;
        }

        $('form').submit();
    });


    function updateDefaultPaymentMethod() {
        const totalAmountText = $('.totalamount').text().trim();
        const totalAmount = parseFloat(totalAmountText.replace('RM', '').replace(',', ''));
        const $bootsPayOption = $('.payment-option[data-method="Boots.Pay"]');
        const $cardOption = $('.payment-option[data-method="Credit / Debit Card"]');

        const bootsPayText = $('.boots-pay-text').text().trim();
        const bootsPay = parseFloat(bootsPayText.replace('RM', '').replace(',', ''));


        const $bootsPayBtn = $('.payment-option[data-method="Boots.Pay"]');
        if (totalAmount > bootsPay) {
            $bootsPayOption.removeClass('active');
            $cardOption.addClass('active');
            $('#paymentMethod').val('Credit / Debit Card');
            $bootsPayBtn.prop('disabled', true);
        } else {
            $cardOption.removeClass('active');
            $bootsPayOption.addClass('active');
            $('#paymentMethod').val('Boots.Pay');
            $bootsPayBtn.prop('disabled', false);
        }
    }

    updateDefaultPaymentMethod();

    $('.checkout-paymentTable-voucher-btn').click(function () {
        $('#checkout-addresspopup-overlay, #voucher-popup-modal').fadeIn();
    });

    $('.voucher-popup-modal-close-btn').click(function () {
        $('#checkout-addresspopup-overlay, #voucher-popup-modal').fadeOut();
    });

    $('.orderRecord-voucher-shop-btn').on('click', function () {
        const voucherId = $(this).data('voucher-id');
        const voucherType = $(this).data('voucher-type');
        const $productTotal = parseFloat($(".product_total").text().replace(/,/g, ''));
        const $shippingFee = parseFloat($(".shipping_fee").text());
        const $discount = $(".voucher_discount");
        const $totalAmountText = $(".totalamount");
        const $amount = parseFloat($(".totalamount").text().replace(/RM/g, '').replace(/,/g, ''));

        if (voucherType === 1 && $productTotal < 99.99) {
            alert("To use this vocuher discount, the total product amount must be at least RM99.99")
            return;
        } else if (voucherType === 2 && $productTotal < 888.88) {
            alert("To use this vocuher discount, the total product amount must be at least RM888.88")
            return;
        } else if (voucherType === 3 && $productTotal < 499.99) {
            alert("To use this vocuher discount, the total product amount must be at least RM499.99")
            return;
        }

        $('#checkout-addresspopup-overlay, #voucher-popup-modal').fadeOut();
        const $discountWord = $('#discount-word');
        const $totalAmountWord = $('#totalAmount-word');
        const $voucherTypeWord = $('#voucherType-word');
        const $voucherDisplayText = $('.voucher-display');
        let totalDiscount = 0;
        let totalAmount = 0;
        let vDisplayText;

        if (voucherType === 1) {
            totalDiscount = $shippingFee;
            totalAmount = $productTotal;
            vDisplayText = "Free Shipping";
        } else if (voucherType === 2) {
            totalDiscount = 100;
            totalAmount = $productTotal + $shippingFee - totalDiscount;
            vDisplayText = "RM100 Off";
        } else if (voucherType === 3) {
            totalDiscount = ($productTotal * 0.15);
            totalAmount = $productTotal + $shippingFee - totalDiscount;
            vDisplayText = "85% Off";
        } else {
            totalDiscount = 0;
            totalAmount = $amount;
        }

        $('.checkout-paymentTable-voucherDisplay').show();
        $voucherDisplayText.text(`${vDisplayText}`);
        $discount.text(`${totalDiscount.toFixed(2)}`);
        $totalAmountText.text(`RM${totalAmount.toFixed(2)}`);

        $discountWord.val(totalDiscount);
        $totalAmountWord.val(totalAmount);
        $voucherTypeWord.val(voucherType);
    });

    $('.checkout-paymentTable-voucherDisplay-close').on('click', function () {
        $('.checkout-paymentTable-voucherDisplay').hide();
        const $productTotal = parseFloat($(".product_total").text().replace(/,/g, ''));
        const $shippingFee = parseFloat($(".shipping_fee").text());
        const $discount = $(".voucher_discount");
        const $totalAmountText = $(".totalamount");

        let totalDiscount = 0;
        let totalAmount = 0;
        totalAmount = $productTotal + $shippingFee;
        $discount.text(`${totalDiscount.toFixed(2)}`);
        $totalAmountText.text(`RM${totalAmount.toFixed(2)}`);


        const $discountWord = $('#discount-word');
        const $totalAmountWord = $('#totalAmount-word');
        const $voucherTypeWord = $('#voucherType-word');
        let voucherType = 0;
        $discountWord.val(totalDiscount);
        $totalAmountWord.val(totalAmount);
        $voucherTypeWord.val(voucherType);
    });


});