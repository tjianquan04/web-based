<?php
require '_base.php';

function getSubtotal($qty, $price)
{
    return $qty * $price;
}

if (is_post()) {
    $orderItemIds = $_POST['order_items'];

    $productTotal = filter_var($_POST['productTotal'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $shippingFee = filter_var($_POST['shippingFee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $discount = filter_var($_POST['discount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $amount = filter_var($_POST['totalAmount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $orderStatus = 'shipping'; 
    $currentDateTime = (new DateTime('now'))->format('Y-m-d H:i:s');
    //$memberId = 'M000001'; //need replace
    $member = $_SESSION['user'];
    authMember($member);
    $memberId =  $member->member_id;

    $paymentMethod = $_POST['payment_method'];
    $paymentStatus = 'completed';

    $voucherType = $_POST['voucherType'];


    //voucher
    // $allVoucherDesc = array("Free Shipping", "RM100 Off", "85% Off");
    // $allVoucherType = array(1, 2, 3);
    // $allVoucherMinSpend = array(99.99, 888.88, 499.99);
    // $allVoucherStatus = 1;
    // $allVoucherPhoto = array("truck.png", "boots.png", "boots.png");
    // $newMemberId = "M000002";

    // foreach ($allVoucherDesc as $index => $voucherDesc) {
    //     $voucherType = $allVoucherType[$index];
    //     $voucherMinSpend = $allVoucherMinSpend[$index];
    //     $voucherPhoto = $allVoucherPhoto[$index];

    //     $stmz = $_db->prepare(
    //         'SELECT voucher_id
    //     FROM voucher
    //     ORDER BY voucher_id DESC
    //     LIMIT 1'
    //     );
    //     $stmz->execute();
    //     $lastVoucherId = $stmz->fetchColumn();

    //     if ($lastVoucherId) {
    //         $outputInt = (intval(substr($lastVoucherId, 1))) + 1;
    //         $newVoucherId = 'V' . str_pad($outputInt, 5, '0', STR_PAD_LEFT);
    //     } else {
    //         $newVoucherId = 'V00001';
    //     }

    //     $stmzz = $_db->prepare(
    //         'INSERT INTO `voucher`(`voucher_id`, `voucher_desc`, `voucher_type`, `min_spend`, `voucher_status`, `voucher_photo`, `member_id`) 
    //         VALUES (?,?,?,?,?,?,?)'
    //     );
    //     $stmzz->execute([$newVoucherId, $voucherDesc, $voucherType, $voucherMinSpend, $allVoucherStatus, $voucherPhoto, $newMemberId]);
    // }
    //end

    $stm1 = $_db->prepare(
        'SELECT order_id
        FROM order_record
        ORDER BY order_id DESC
        LIMIT 1'
    );
    $stm1->execute();
    $lastOrderRecordId = $stm1->fetchColumn();

    if ($lastOrderRecordId) {
        $outputInt = (intval(substr($lastOrderRecordId, 1))) + 1;
        $newOrderRecordId = 'R' . str_pad($outputInt, 5, '0', STR_PAD_LEFT);
    } else {
        $newOrderRecordId = 'R00001';
    }

    $stm2 = $_db->prepare(
        'SELECT *
        from member
        INNER JOIN `address`
        on member.member_id = address.member_id
        where member.member_id = ?
        AND address.is_default = 1'
    );
    $stm2->execute([$memberId]);
    $member = $stm2->fetch();

    $stm3 = $_db->prepare(
        'INSERT INTO `order_record`(`order_id`, `product_total`, `shipping_fee`, `discount`, `total_amount`, `order_status`, `order_date`, `address_line`, `postal_code`, `state`, `member_id`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)'
    );
    $result = $stm3->execute([$newOrderRecordId, $productTotal, $shippingFee, $discount, $amount, $orderStatus, $currentDateTime, $member->address_line, $member->postal_code, $member->state, $memberId]);

    $stm4 = $_db->prepare(
        'SELECT payment_id
            FROM payment
            ORDER BY payment_id DESC
            LIMIT 1'
    );
    $stm4->execute();
    $lastPaymentId = $stm4->fetchColumn();

    if ($lastPaymentId) {
        $outputInt = (intval(substr($lastPaymentId, 1))) + 1;
        $newPaymentId = 'P' . str_pad($outputInt, 5, '0', STR_PAD_LEFT);
    } else {
        $newPaymentId = 'P00001';
    }

    $stm5 = $_db->prepare(
        'INSERT INTO `payment`(`payment_id`, `total_amount`, `payment_method`, `payment_status`, `order_id`)
        VALUES (?,?,?,?,?)'
    );
    $stm5->execute([$newPaymentId, $amount, $paymentMethod, $paymentStatus, $newOrderRecordId]);

    $stm6 = $_db->prepare(
        'UPDATE voucher
        SET voucher_status = 0
        WHERE voucher_type = ?
        AND member_id = ?'
    );
    $stm6->execute([$voucherType, $memberId]);

    if ($paymentMethod == "Boots.Pay") {
        $stm7 = $_db->prepare(
            'UPDATE member
        SET wallet = wallet - ?
        WHERE member_id = ?'
        );
        $stm7->execute([$amount, $memberId]);
    }

    foreach ($orderItemIds as $s) {
        $stm3 = $_db->prepare(
            'SELECT orderItem_id
            FROM orderitem
            ORDER BY orderItem_id DESC
            LIMIT 1'
        );
        $stm3->execute();
        $lastOrderItemId = $stm3->fetchColumn();

        if ($lastOrderItemId) {
            $outputInt2 = (intval(substr($lastOrderItemId, 1))) + 1;
            $newOrderItemId = 'O' . str_pad($outputInt2, 5, '0', STR_PAD_LEFT);
        } else {
            $newOrderItemId = 'O00001';
        }

        $stm1 = $_db->prepare(
            'SELECT * 
            FROM cartitem
            INNER JOIN product
            on  cartitem.product_id = product.product_id
            WHERE cartItem_id = ?'
        );
        $stm1->execute([$s]);
        $item = $stm1->fetch();

        $stm2 = $_db->prepare(
            'INSERT INTO `orderitem`(`orderItem_id`, `quantity`, `total_price`, `order_id`, `product_id`) 
            VALUES (?,?,?,?,?)'
        );
        $subtotal = getSubtotal($item->quantity, $item->unit_price);
        $stm2->execute([$newOrderItemId, $item->quantity, $subtotal, $newOrderRecordId, $item->product_id]);

        $stm3 = $_db->prepare(
            'DELETE from cartitem
            WHERE cartItem_id = ?'
        );
        $stm3->execute([$s]);

        $stm10 = $_db->prepare(
            'UPDATE product
            SET stock_quantity = stock_quantity - ?
            WHERE product_id = ?'
        );
        $stm10->execute([$item->quantity, $item->product_id]);
    }
    $trans_id = generateTransactionId();
    $stmt = $_db->prepare('INSERT INTO transactions (trans_id, trans_date, trans_amount, trans_type, trans_status, reference, member_id) VALUES (?, ?, ?, ?, ? , ?, ?)');
    $stmt->execute([$trans_id, $currentDateTime, $amount, "Purchase", "Pending", $newOrderRecordId, $memberId]);

    header('Location: order_record.php?section=.orderRecord-right-toship');
    exit;
}
