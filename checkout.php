<?php
require '_base.php';

function getSubtotal($qty, $price)
{
    return $qty * $price;
}

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

$member = $_SESSION['user'];
authMember($member);
$id =  $member->member_id;
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

$stm2 = $_db->prepare(
    'SELECT *
    from address
    where member_id = ?
    AND is_default = 0'
);
$stm2->execute([$id]);
$address = $stm2->fetchall();

$stm3 = $_db->prepare(
    'SELECT *
    FROM voucher
    WHERE member_id = ?
    AND voucher_status = 1'
);
$stm3->execute([$id]);
$voucher_arr = $stm3->fetchAll();

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
             where cartItem_id = ?
             AND product_photo.default_photo = 1'
        );
        $stm->execute([$s]);
        $arr[] = $stm->fetch();
    }
}

$productTotal = 0;
$shippingFee = 1.99 * count($arr);

$_title = 'Boots.Do | Checkout';
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
                <?= $member->name ?> (+<?= getFirstTwoDigit($member->contact) ?>)
                <br>
                <?= getAfterFirstTwoDigit($member->contact) ?>
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

<div id="checkout-addresspopup-overlay"></div>
<div id="checkout-addresspopup-modal">
    <table class="checkout-addresspopup-table">
        <tr>
            <td class="checkout-addresspopup-title" colspan="3"><i class="fa-solid fa-location-dot"></i> <b>My Address</b></td>
        </tr>
        <tr>
            <td class="checkout-addresspopup-radio"><input type="radio" data-ori-address-id="<?= $member->address_id ?>" data-address-id="<?= $member->address_id ?>" class="checkout-addresspopup-radio-btn" name="address" checked></td>
            <td class="checkout-addresspopup-address">
                <b><?= $member->name ?></b>
                <span class="checkout-addresspopup-addressinfo">
                    (+<?= getFirstTwoDigit($member->contact) ?>)
                    <?= getAfterFirstTwoDigit($member->contact) ?> <br>
                    <?= $member->address_line ?>,
                    <?= $member->postal_code ?>,
                    <?= $member->state ?> <br>
                </span>
                <div id="checkout-addresspopup-defaultaddress">Default</div>
            </td>
            <td class="checkout-addresspopup-edit"><button class="checkout-addresspopup-edit-btn"
                    data-address-id="<?= $member->address_id ?>"
                    data-address-line="<?= $member->address_line ?>"
                    data-postal-code="<?= $member->postal_code ?>"
                    data-state="<?= $member->state ?>">Edit</button></td>
        </tr>
        <tr>
            <td colspan="3">
                <hr>
            </td>
        </tr>
        <?php foreach ($address as $a) : ?>
            <tr>
                <td class="checkout-addresspopup-radio"><input type="radio" data-ori-address-id="<?= $member->address_id ?>" data-address-id="<?= $a->address_id ?>" class="checkout-addresspopup-radio-btn" name="address"></td>
                <td class="checkout-addresspopup-address">
                    <b><?= $member->name ?></b>
                    <span class="checkout-addresspopup-addressinfo">
                        (+<?= getFirstTwoDigit($member->contact) ?>)
                        <?= getAfterFirstTwoDigit($member->contact) ?> <br>
                        <?= $a->address_line ?>,
                        <?= $a->postal_code ?>,
                        <?= $a->state ?>
                    </span>
                </td>
                <td class="checkout-addresspopup-edit"><button class="checkout-addresspopup-edit-btn"
                        data-address-id="<?= $a->address_id ?>"
                        data-address-line="<?= $a->address_line ?>"
                        data-postal-code="<?= $a->postal_code ?>"
                        data-state="<?= $a->state ?>">Edit</button></td>
            </tr>
            <tr>
                <td colspan=" 3">
                    <hr>
                </td>
            </tr>
        <?php endforeach ?>
        <tr>
            <td colspan="3" class="checkout-addresspopup-addnew"><button class="checkout-addresspopup-addnew-btn"><i class="fa-solid fa-plus"></i> Add New Address</button></td>
        </tr>
    </table>
    <table class="checkout-addresspopup-table-action">
        <tr>
            <td class="checkout-addresspopup-emptyspace"></td>
            <td class="checkout-addresspopup-cancel"><button id="checkout-addresspopup-cancel-btn">Cancel</button></td>
            <td class="checkout-addresspopup-confirm"><button type="submit" id="checkout-addresspopup-confirm-btn">Confirm</button></td>
        </tr>
    </table>
</div>


<div id="checkout-editpopup-modal">
    <input type="hidden" id="addressId" name="address_id" value="">
    <table>
        <tr>
            <td class="checkout-editpopup-title" colspan="2"><b>Edit Address</b></td>
        </tr>
        <tr>
            <td class="checkout-editpopup-fixedinfo">
                <fieldset>
                    <legend class="checkout-editpopup-infolabel">Full Name</legend>
                    <?= $member->name ?>
                </fieldset>
            </td>
            <td class="checkout-editpopup-fixedinfo">
                <fieldset>
                    <legend class="checkout-editpopup-infolabel">Phone Number</legend>
                    (+<?= getFirstTwoDigit($member->contact) ?>) <?= getAfterFirstTwoDigit($member->contact) ?>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="checkout-editpopup-addressinfo" colspan="2">
                <fieldset>
                    <legend class="checkout-editpopup-infolabel">Address Line</legend>
                    <input type="text" class="input-box" id="addressLine" name="address_line" value="<?= $member->address_line ?>">
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="checkout-editpopup-addressinfo" colspan="2">
                <fieldset>
                    <legend class="checkout-editpopup-infolabel">Postal Code</legend>
                    <input type="text" class="input-box" id="postalCode" name="postal_code" value="<?= $member->postal_code ?>">
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="checkout-editpopup-addressinfo" colspan="2">
                <fieldset>
                    <legend class="checkout-editpopup-infolabel">State</legend>
                    <input type="text" class="input-box" id="state" name="state" value="<?= $member->state ?>">
                </fieldset>
            </td>
        </tr>
    </table>
    <table class="checkout-editpopup-table-action">
        <tr>
            <td class="checkout-editpopup-emptyspace"></td>
            <td class="checkout-editpopup-cancel"><button id="checkout-editpopup-cancel-btn">Cancel</button></td>
            <td class="checkout-editpopup-confirm"><button type="submit" id="checkout-editpopup-confirm-btn">Confirm</button></td>
        </tr>
    </table>
</div>

<div id="checkout-addaddress-modal">
    <table>
        <tr>
            <td class="checkout-addaddress-title" colspan="2"><b>Add New Address</b></td>
        </tr>
        <tr>
            <td class="checkout-addaddress-fixedinfo">
                <fieldset>
                    <legend class="checkout-addaddress-infolabel">Full Name</legend>
                    <?= $member->name ?>
                </fieldset>
            </td>
            <td class="checkout-addaddress-fixedinfo">
                <fieldset>
                    <legend class="checkout-addaddress-infolabel">Phone Number</legend>
                    (+<?= getFirstTwoDigit($member->contact) ?>) <?= getAfterFirstTwoDigit($member->contact) ?>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="checkout-addaddress-addressinfo" colspan="2">
                <fieldset>
                    <legend class="checkout-addaddress-infolabel">Address Line</legend>
                    <input type="text" class="input-box" id="newAddressLine" placeholder="Address Line">
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="checkout-addaddress-addressinfo" colspan="2">
                <fieldset>
                    <legend class="checkout-addaddress-infolabel">Postal Code</legend>
                    <input type="text" class="input-box" id="newPostalCode" placeholder="Postal Code">
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="checkout-addaddress-addressinfo" colspan="2">
                <fieldset>
                    <legend class="checkout-addaddress-infolabel">State</legend>
                    <input type="text" class="input-box" id="newState" placeholder="State">
                </fieldset>
            </td>
        </tr>
    </table>
    <table class="checkout-addaddress-table-action">
        <tr>
            <td class="checkout-addaddress-emptyspace"></td>
            <td class="checkout-addaddress-cancel"><button id="checkout-addaddress-cancel-btn">Cancel</button></td>
            <td class="checkout-addaddress-confirm"><button type="submit" id="checkout-addaddress-confirm-btn">Confirm</button></td>
        </tr>
    </table>
</div>

<br>
<form method="post" action="placeOrder.php">
    <table class="checkout-productTable">
        <tr>
            <td class="checkout-productTable-columnheader1">Products Ordered</td>
            <td class="checkout-productTable-columnheader2">Unit Price</td>
            <td class="checkout-productTable-columnheader2">Quantity</td>
            <td class="checkout-productTable-columnheader2">Subtotal</td>
        </tr>
        <?php foreach ($arr as $product): ?>
            <input type="hidden" name="order_items[]" value="<?= $product->cartItem_id ?>">
            <tr>
                <td class="checkout-productTable-product">
                    <label class="checkout-productTable-productphoto">
                        <img src="/product_gallery/<?= $product->product_photo_id ?>">
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
    <input type="hidden" name="productTotal" value="<?= number_format($productTotal, 2) ?>">
    <input type="hidden" name="shippingFee" value=" <?= number_format($shippingFee, 2) ?>">
    <input type="hidden" name="discount" id="discount-word" value="0">
    <input type="hidden" name="totalAmount" id="totalAmount-word" value="<?= number_format(($productTotal + $shippingFee), 2) ?>">
    <input type="hidden" name="voucherType" id="voucherType-word" value="0">
    <table class="checkout-paymentTable">
        <tr>
            <td class="checkout-paymentTable-vouchertext"><i class="fa-solid fa-ticket"></i> Voucher / Discount</td>
            <td>
                <div class="checkout-paymentTable-voucherDisplay" style="display: none;">
                    <span class="voucher-display">Voucher Display</span>
                    <button type="button" class="checkout-paymentTable-voucherDisplay-close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </td>
            <td class="checkout-paymentTable-voucher"><button class="checkout-paymentTable-voucher-btn">Select Voucher</button></td>
        </tr>
        <tr>
            <td colspan="3" class="checkout-paymentTable-hrline">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="checkout-paymentTable-paymentmethod">
                <input type="hidden" name="payment_method" id="paymentMethod" value="Boots.Pay">
                <div class="checkout-paymentTable-paymentmethodtext">Payment Method</div>
                <button class="payment-option active" data-method="Boots.Pay">Boots.Pay (<span class="boots-pay-text">RM<?= $member->wallet ?></span>)</button>
                <button class="payment-option" data-method="Credit / Debit Card">Credit / Debit Card</button>

            </td>
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
                RM<span class="product_total"><?= number_format($productTotal, 2) ?></span><br>
                RM<span class="shipping_fee"><?= number_format($shippingFee, 2) ?></span><br>
                -RM<span class="voucher_discount">0.00</span><br>
                <span style="font-size: 25px;" class="totalamount">RM<?= number_format(($productTotal + $shippingFee), 2) ?></span>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="checkout-paymentTable-hrline">
                <hr>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="checkout-placeOrder"><button type="submit" class="checkout-placeOrder-btn"><b>Place Order</b></button></td>
        </tr>
    </table>

    <div id="voucher-popup-modal" style="display: none;">
        <table class="voucher-popup-modal-table">
            <tr>
                <td class="voucher-popup-modal-title">Your Vouchers</td>
                <td class="voucher-popup-modal-close-btn">
                    <i class="fa-solid fa-xmark"></i>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <?php foreach ($voucher_arr as $voucher): ?>
                <tr>
                    <td colspan="2">
                        <div class="orderRecord-member-voucher">
                            <div class="orderRecord-voucher-photo">
                                <label class="voucher-photo"><img src="/image/<?= $voucher->voucher_photo ?>"></label>
                            </div>
                            <div class="orderRecord-voucher-desc">
                                <?= $voucher->voucher_desc ?><br>
                                <span style="color: rgb(120,120,120);">Min. Spend RM<?= $voucher->min_spend ?></span>
                            </div>
                            <div class="orderRecord-voucher-shop">
                                <div class="orderRecord-voucher-shop-wrapper">
                                    <div class="orderRecord-voucher-shop-btn" data-voucher-id="<?= $voucher->voucher_id ?>" data-voucher-type="<?= $voucher->voucher_type ?>">Use</div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>
    </div>

    <div id="cardDetailsModal" style="display: none;">
        <table class="checkout-cardpopup-table">
            <tr>
                <td class="checkout-cardpopup-title" colspan="3"><b>Pay by Credit / Debit Card</b></td>
            </tr>
            <tr>
                <td colspan="3" class="checkout-cardpopup-cardnumber">
                    <label for="cardNumber" class="checkout-cardpopup-infolabel">Card Number</label><br>
                    <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 1234 1234 1234" required autocomplete="off">
                </td>
            </tr>
            <tr>
                <td class="checkout-cardpopup-expiry">
                    <label for="cardExpiry" class="checkout-cardpopup-infolabel">Expiry</label>
                    <input type="text" id="cardExpiry" name="cardExpiry" placeholder="MM/YY" required autocomplete="off">
                </td>
                <td class="checkout-cardpopup-empty"></td>
                <td class="checkout-cardpopup-cvv">
                    <label for="cardCVV" class="checkout-cardpopup-infolabel">CVV</label>
                    <input type="text" id="cardCVV" name="cardCVV" placeholder="CVV" required autocomplete="off">
                </td>
            </tr>
            <tr>
                <td class="checkout-cardpopup-country">
                    <label for="cardCountry" class="checkout-cardpopup-infolabel">Country</label>
                    <input type="text" id="cardCountry" name="cardCountry" placeholder="Country" list="country" required autocomplete="off">
                    <datalist id="country">
                        <option value="Malaysia">
                        <option value="United States">
                        <option value="China">
                        <option value="India">
                        <option value="Canada">
                        <option value="Australia">
                        <option value="United Kingdom">
                        <option value="Germany">
                        <option value="France">
                        <option value="Japan">
                        <option value="South Korea">
                        <option value="Singapore">
                        <option value="Italy">
                        <option value="Spain">
                        <option value="Brazil">
                        <option value="South Africa">
                        <option value="Mexico">
                        <option value="Russia">
                        <option value="Netherlands">
                        <option value="Switzerland">
                    </datalist>
                </td>
                <td class="checkout-cardpopup-empty"></td>
                <td class="checkout-cardpopup-zipcode">
                    <label for="cardZipCode" class="checkout-cardpopup-infolabel">Zip</label>
                    <input type="text" id="cardZipCode" name="cardZipCode" placeholder="12345" required autocomplete="off">
                </td>
            </tr>
            <tr>
                <td class="checkout-cardpopup-pay" colspan="3"><button type="submit" class="checkout-cardpopup-pay-btn" id="pay-btn">Pay Now</button></td>
            </tr>
            <tr>
                <td class="checkout-cardpopup-cancel" colspan="3"><button type="button" class="checkout-cardpopup-cancel-btn" id="closeCardModal">Cancel</button></td>
            </tr>
        </table>
    </div>
</form>




<?php
include '_foot.php';
