<?php
require '_base.php';

//change to get current id
$id = "M000001";
$stm = $_db->prepare(
    'SELECT cartitem.cartItem_id, product_photo.photo, product.description, product.unit_price, cartItem.quantity
     from cartitem
     INNER JOIN product 
     on  cartitem.product_id = product.product_id
     INNER JOIN product_photo
     on product_photo.product_id = product.product_id
     where cartitem.member_id = ?'
);
$stm->execute([$id]);
$arr = $stm->fetchAll();

//calculate subtotal for one item
function getSubtotal($qty, $price)
{
    return $qty * $price;
}

$_title = 'Cart';
include '_head.php';
?>

<link rel="stylesheet" href="/css/cart.css">
<script src="/js/shopping_cart.js"></script>

<div class="cart-title">
    <h1>Boots.Do | Shopping Cart</h1>
</div>
<br>

<form method="POST" action="checkout.php">
    <table class="cart">
        <tr>
            <td class="checkBox">
                <input type="checkbox" id="select-all-top" class="checkall">
            </td>
            <td class="item_desc">
                Product
            </td>
            <td class="item_details">
                Unit Price
            </td>
            <td class="item_details">
                Quantity
            </td>
            <td class="item_details">
                Total Price
            </td>
            <td class="item_details">
                Actions
            </td>
        </tr>
        <tr class="emptySpace">
            <td colspan="6">
                <hr>
            </td>
        </tr>
        <?php foreach ($arr as $cartItem): ?>
            <tr class="itemRow">
                <td class="checkBox">
                    <input type="checkbox" class="item-checkbox" name="selected_items[]" value="<?= $cartItem->cartItem_id ?>" data-unit-price="<?= $cartItem->unit_price ?>" data-quantity="<?= $cartItem->quantity ?>">
                </td>
                <td class="item_desc">
                    <label class="upload">
                        <img src="/photos/<?= $cartItem->photo ?>">
                    </label>
                    <?= $cartItem->description ?>
                </td>
                <td class="item_details"><?= $cartItem->unit_price ?></td>
                <td class="item_details">
                    <button type="button" class="decrement-btn" data-cartItem-id="<?= $cartItem->cartItem_id ?>"><i class=" fa-solid fa-minus"></i></button>
                    <span id="quantity-display" class="quantity-display" data-cartItem-id="<?= $cartItem->cartItem_id ?>"><?= $cartItem->quantity ?></span>
                    <button type="button" class="increment-btn" data-cartItem-id="<?= $cartItem->cartItem_id ?>"><i class=" fa-solid fa-plus"></i></button>
                </td>
                <td class=" item_details">RM<?= number_format(getSubtotal($cartItem->quantity, $cartItem->unit_price), 2) ?></td>
                <td class="item_details"><button class="delete-button" data-post="cartItem_delete.php?id=<?= $cartItem->cartItem_id ?>">Delete</button></td>
            </tr>
            <tr class="emptySpace">
                <td colspan="6">
                    <hr class="hLine">
                </td>
            </tr>
        <?php endforeach ?>
    </table>

    <table class="amount_container">
        <tr>
            <td class="checkBox">
                <input type="checkbox" id="select-all-footer" class="checkall">
            </td>
            <td class="select-all-text">Select All (<?= count($arr) ?>)</td>
            <td>
                <button class="delete-all-btn">Delete (0)</button>
            </td>
            <td class=" totalitem">Total (0 Item): RM
            </td>
            <td class="totalamount">0.00</td>
            <td class="checkout">
                <button type="submit" class="checkout-btn"><b>Check Out</b></button>
            </td>
        </tr>
    </table>
</form>


<?php
include '_foot.php';
