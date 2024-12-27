<?php
require '_base.php';

function getFirstTwoDigit($contact)
{
    $str =  strval($contact);
    return substr($str, 0, 2);
}

function getAfterFirstTwoDigit($contact)
{
    $str =  strval($contact);
    return substr($str, 2);
}

$id = "M000001";
$stm = $_db->prepare(
    'SELECT *
    FROM member
    WHERE member_id = ?'
);
$stm->execute([$id]);
$member = $stm->fetch();

if (is_post()) {
    $orderId = $_POST['orderId'];
    $option = $_POST['headerbtn-option'];

    $stm1 = $_db->prepare(
        'SELECT *
        FROM orderitem
        INNER JOIN product
        on orderitem.product_id = product.product_id
        INNER JOIN product_photo
        on product_photo.product_id = product.product_id
        where orderitem.order_id = ?'
    );
    $stm1->execute([$orderId]);
    $orderItem_arr = $stm1->fetchAll();

    $stm2 = $_db->prepare(
        'SELECT *
        FROM order_record
        INNER JOIN payment
        on order_record.order_id = payment.order_id
        where order_record.order_id = ?'
    );
    $stm2->execute([$orderId]);
    $orderRecord = $stm2->fetch();

    $formatted_date = date("Y-m-d H:i", strtotime($orderRecord->order_date));
    $arrivedDate = date("Y-m-d", strtotime($formatted_date . ' +3 days'));
}

$_title = 'Boots.Do | Order Record Details';
include '_head.php';
?>

<link rel="stylesheet" href="/css/order_record.css">
<script src="/js/order_record.js"></script>
<br>
<br>

<div class="orderDetails-overall-container">

    <div class="orderDetails-left">
        <div class="orderDetails-member">
            <?= $member->name ?> <br>
        </div>
        <a href="order_record.php?section=.orderRecord-right-<?= $option ?>" class="orderDetails-purchase-btn">
            <div class="orderDetails-purchase active2" data-target=".orderRecord-purchase-content">
                <i class="fa-solid fa-list"></i>&nbsp;&nbsp;&nbsp;My purchase
            </div>
        </a>
        <a href="order_record.php?section=.orderRecord-voucher-content" class="orderDetails-purchase-btn">
            <div class="orderDetails-voucher" data-target=".orderRecord-voucher-content">
                <i class="fa-solid fa-ticket"></i>&nbsp;&nbsp;&nbsp;My Voucher
            </div>
        </a>
    </div>

    <div class="orderDetails-table">
        <table>
            <tr>
                <td colspan="2" class="orderDetails-back"><a href="order_record.php?section=.orderRecord-right-<?= $option ?>" class="orderDetails-back-btn"><i class="fa-solid fa-less-than"></i></i>&nbsp;&nbsp;BACK</a></td>
                <td class="orderDetails-order-status"><?= $orderRecord->order_status ?></td>
            </tr>
            <tr>
                <td colspan="3">
                    <hr>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="orderDetails-table-address-and-orderprocess">
                    <table>
                        <tr>
                            <td class="orderDetails-table-address">
                                <div class="orderDetails-table-addresstext">Delivery Address</div>
                                <div class="orderDetails-table-name"><?= $member->name ?></div>
                                <div class="orderDetails-table-contact">(+<?= getFirstTwoDigit($member->contact) ?>) <?= getAfterFirstTwoDigit($member->contact) ?></div>
                                <div class="orderDetails-table-address1"><?= $orderRecord->address_line ?>,</div>
                                <div class="orderDetails-table-address2"><?= $orderRecord->postal_code ?>, <?= $orderRecord->state ?></div>
                            </td>
                            <td class="orderDetails-table-orderprocess">
                                <div class="orderDetails-table-allprocess">

                                    <div class=" orderDetails-table-process">
                                        <i class="fa-regular fa-clipboard orderprocess-icon"></i><br>
                                        Order Placed<br>
                                        <span style="font-size: 15px;"><?= $formatted_date ?></span>
                                    </div>
                                    <div class="orderDetails-table-process">
                                        <i class="fa-solid fa-coins orderprocess-icon"></i><br>
                                        Order Paid<br>
                                        <span style="font-size: 15px;"><?= $formatted_date ?></span>
                                    </div>
                                    <div class="orderDetails-table-process">
                                        <i class="fa-solid fa-truck orderprocess-icon"></i><br>
                                        Order Shipped Out<br>
                                        <span style="font-size: 15px;"><?= $arrivedDate ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <hr>
                </td>
            </tr>
            <?php foreach ($orderItem_arr as $item_arr): ?>
                <tr>
                    <td class="item_info">
                        <label class="upload"><img src="/photos/<?= $item_arr->photo ?>"></label>
                        <?= $item_arr->description ?>
                    </td>
                    <td class="item_qtyPrice">
                        RM<?= $item_arr->unit_price ?>
                    </td>
                    <td class="item_qtyPrice">
                        x<?= $item_arr->quantity ?>
                    </td>
                </tr>
            <?php endforeach ?>
            <tr>
                <td colspan="3">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="orderDetails-table-payment-text">
                    Product Total:<br>
                    Shipping Fee:<br>
                    Voucher Discount:<br>
                    Total Payment:<br>
                    Payment Method:
                </td>
                <td class="orderDetails-table-payment" colspan="2">
                    RM<?= $orderRecord->product_total ?><br>
                    RM<?= $orderRecord->shipping_fee ?><br>
                    -RM<?= $orderRecord->discount ?><br>
                    <span style="font-size: 25px; color:rgb(0, 0, 255);">RM<?= $orderRecord->total_amount ?><br></span>
                    <?= $orderRecord->payment_method ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php
include '_foot.php';
