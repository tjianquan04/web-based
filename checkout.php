<?php
require '_base.php';

function getSubtotal($qty, $price)
{
    return $qty * $price;
}

//change to get current id
$id = "M000001";
$stm = $_db->prepare(
    'SELECT *
     from member
     INNER JOIN address
     on member.member_id = address.member_id
     where member.member_id = ?
     AND address.is_default = 1'
);
$stm->execute([$id]);
$member = $stm->fetch();

if (is_post()) {
    $selectedItems = $_POST['selected_items'];
    $arr = [];
    foreach ($selectedItems as $s) {
        $stm = $_db->prepare(
            'SELECT *
             from cartitem
             INNER JOIN product 
             on  cartitem.product_id = product.product_id
             INNER JOIN product_photo
             on product_photo.product_id = product.product_id
             where cartItem_id = ?'
        );
        $stm->execute([$s]);
        $arr[] = $stm->fetch();
    }
}

$productTotal = 0;
$shippingFee = 1.99 * count($arr);

$_title = 'checkout';
include '_head.php';
?>

<link rel="stylesheet" href="/css/cart.css">
<script src="/js/shopping_cart.js"></script>

<div class="checkout-title">
    <h1>Boots.Do | Checkout</h1>
</div>

<table class="checkout-addressTable">
    <tr>
        <td colspan="3" class="checkout-addressTable-a"><i class="fa-solid fa-location-dot"></i><b> Delivery Address</b></td>
    </tr>
    <tr>
        <td class="checkout-addressTable-b">
            <b>
                <?= $member->name ?>
                <br>
                <?= $member->contact ?>
            </b>
        </td>
        <td class="checkout-addressTable-c">
            <?= $member->address_line ?>,
            <?= $member->postal_code ?>,
            <?= $member->state ?>
        </td>
        <td class="checkout-addressTable-d"><button class="checkout-addressTable-d-btn">Change</button></td>
    </tr>
</table>

<br>

<table class="checkout-productTable">
    <tr>
        <td class="checkout-productTable-columnheader1">Products Ordered</td>
        <td class="checkout-productTable-columnheader2">Unit Price</td>
        <td class="checkout-productTable-columnheader2">Quantity</td>
        <td class="checkout-productTable-columnheader2">Subtotal</td>
    </tr>
    <?php foreach ($arr as $product): ?>
        <tr>
            <td class="checkout-productTable-product">
                <label class="checkout-productTable-productphoto">
                    <img src="/photos/<?= $product->photo ?>">
                </label>
                <?= $product->description ?>
            </td>
            <td class="checkout-productTable-productdetails"><?= $product->unit_price ?></td>
            <td class="checkout-productTable-productdetails"><?= $product->quantity ?></td>
            <td class="checkout-productTable-productdetails">RM<?= number_format(getSubtotal($product->quantity, $product->unit_price), 2) ?></td>
        </tr>
    <?php
        $productTotal += getSubtotal($product->quantity, $product->unit_price);
    endforeach
    ?>
</table>

<br>

<table class="checkout-paymentTable">
    <tr>
        <td class="checkout-paymentTable-vouchertext"><i class="fa-solid fa-ticket"></i> Voucher / Discount</td>
        <td></td>
        <td class="checkout-paymentTable-voucher"><button class="checkout-paymentTable-voucher-btn">Select Voucher</button></td>
    </tr>
    <tr>
        <td colspan="3" class="checkout-paymentTable-hrline">
            <hr>
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="checkout-paymentTable-paymenttext">
            Product Total: <br>
            Shipping Fee: <br>
            Voucher Discount: <br>
            Total Payment:
        </td>
        <td class="checkout-paymentTable-amount">
            RM<?= number_format($productTotal, 2) ?> <br>
            RM<?= number_format($shippingFee, 2) ?> <br>
            -RM5.00 <br>
            <span style="font-size: 25px;">RM<?= number_format(($productTotal + $shippingFee), 2) ?></span>
        </td>
    </tr>
    <tr>
        <td colspan="3" class="checkout-paymentTable-hrline">
            <hr>
        </td>
    </tr>
</table>



<?php
include '_foot.php';
