$(() => {

    

    $(".orderRecord-voucher").click(function () {
        $(".orderRecord-purchase").removeClass("active1");

        $(".orderRecord-voucher").addClass("active1");

        $('.orderRecord-right1').hide();

        $('.orderRecord-right2').show();
    });

    $(".orderRecord-purchase").click(function () {
        $(".orderRecord-voucher").removeClass("active1");

        $(".orderRecord-purchase").addClass("active1");

        $('.orderRecord-right2').hide();

        $('.orderRecord-right1').show();
    });

    $(".orderRecord-right-header-btn").click(function () {
        $(".orderRecord-right-header-btn").removeClass("active");

        $(this).addClass("active");

        $(".orderRecord-right-content > div").hide();

        $($(this).data("target")).show();
    });

    // Show "All" section by default
    const targetSection = new URLSearchParams(window.location.search).get('section') || '.orderRecord-right-all';
    if (targetSection === ".orderRecord-right-all" || targetSection === ".orderRecord-right-toship" || targetSection === ".orderRecord-right-toreceive" || targetSection === ".orderRecord-right-completed") {
        $(".orderRecord-right-header-btn").removeClass("active");
        $(`[data-target="${targetSection}"]`).addClass("active");
        $(".orderRecord-right-content > div").hide();
        $(targetSection).show();
    } else if (targetSection === ".orderRecord-voucher-content") {
        $(".orderRecord-purchase").removeClass("active1");

        $(".orderRecord-voucher").addClass("active1");

        $('.orderRecord-right1').hide();

        $('.orderRecord-right2').show();
    }

    $('.order_received-btn').click(function () {
        const orderId = $(this).data('order-id');

        $.ajax({
            url: 'update_order_status.php',
            type: 'POST',
            data: { orderId: orderId },
            success: function (response) {
                if (response.trim() === 'Success') {
                    // Reload the page with a query parameter to navigate to "Completed"
                    setTimeout(() => {
                        window.location.href = window.location.pathname + '?section=.orderRecord-right-completed';
                    }, 1);
                } else {
                    alert('Failed to update order status. Please try again.');
                }
            },
            error: function () {
                alert('Failed to update order status.');
            }
        });
    });

    $(".order_rating-btn").click(function () {
        const orderId = $(this).data('order-id'); // Get the order ID from the data attribute

        // Find the corresponding modal and show it
        const modal = $("#order-rating-modal-" + orderId); // Get the modal with that order ID

        // Fade in the modal
        modal.fadeIn(); // Use fadeIn with class toggle for smooth transition
        $('#order-rating-overlay').fadeIn();
    });
    
    $(".order-rating-modal-cancel-btn").click(function () {
        $(this).closest(".order-rating-modal").fadeOut();
        $('#order-rating-overlay').fadeOut();
    });

    $(".order-rating-table-rate-star .star").click(function () {
        const selectedValue = $(this).data("value"); // Get the value of the clicked star
        const itemId = $(this).data("item-id");
        const $rateWord = $(`.order-rating-table-rate-word[data-item-id="${itemId}"]`);
        const $rateWordInput = $(`#rate-word-${itemId}`);

        let ratingText = "";

        if (selectedValue === 1) {
            ratingText = "Terrible";
        } else if (selectedValue === 2) {
            ratingText = "Poor";
        } else if (selectedValue === 3) {
            ratingText = "Fair";
        } else if (selectedValue === 4) {
            ratingText = "Good";
        } else {
            ratingText = "Amazing";
        }

        $rateWord.text(ratingText);
        $rateWordInput.val(ratingText);
        // Loop through all stars and update their classes
        $(this).parent().find(".star").each(function () {
            const starValue = $(this).data("value");

            if (starValue <= selectedValue) {
                // Set filled star for values less than or equal to the selected value
                $(this).find("i").removeClass("fa-regular").addClass("fa-solid");
            } else {
                // Set empty star for values greater than the selected value
                $(this).find("i").removeClass("fa-solid").addClass("fa-regular");
            }
        });
    });
});