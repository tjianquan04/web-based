<?php

//use Stripe\Climate\Order;

require '_base.php';

$id = "M000001";
$stm = $_db->prepare(
    'SELECT *
    FROM member
    WHERE member_id = ?'
);
$stm->execute([$id]);
$member = $stm->fetch();

$stm2 = $_db->prepare(
    'SELECT *
    FROM order_record
    WHERE member_id = ?
    ORDER BY order_id desc'
);
$stm2->execute([$id]);
$orderAll_arr = $stm2->fetchAll();

$stm3 = $_db->prepare(
    'SELECT *
    FROM order_record
    WHERE member_id = ?
    AND order_status = "shipping"
    ORDER BY order_id desc'
);
$stm3->execute([$id]);
$orderShip_arr = $stm3->fetchAll();

$stm4 = $_db->prepare(
    'SELECT *
    FROM order_record
    WHERE member_id = ?
    AND order_status = "delivered"
    ORDER BY order_id desc'
);
$stm4->execute([$id]);
$orderReceive_arr = $stm4->fetchAll();

$stm5 = $_db->prepare(
    'SELECT *
    FROM order_record
    WHERE member_id = ?
    AND order_status = "completed"
    ORDER BY order_id desc'
);
$stm5->execute([$id]);
$orderCompleted_arr = $stm5->fetchAll();

$stm6 = $_db->prepare(
    'SELECT *
    FROM voucher
    WHERE member_id = ?
    AND voucher_status = 1'
);
$stm6->execute([$id]);
$voucher_arr = $stm6->fetchAll();

if (is_post()) {
    $orderId = $_POST['orderId'];
}

$_title = 'Boots.Do | Order Record';
include '_head.php';
?>

<link rel="stylesheet" href="/css/order_record.css">
<script src="/js/order_record.js"></script>
<br>
<br>

<div class="orderRecord-overall-container">

    <div class="orderRecord-left">
        <div class="orderRecord-member">
            <?= $member->name ?> <br>
        </div>
        <div class="orderRecord-purchase active1" data-target=".orderRecord-purchase-content">
            <i class="fa-solid fa-list"></i>&nbsp;&nbsp;&nbsp;My purchase
        </div>
        <div class="orderRecord-voucher" data-target=".orderRecord-voucher-content">
            <i class="fa-solid fa-ticket"></i>&nbsp;&nbsp;&nbsp;My Voucher
        </div>
    </div>

    <div class="orderRecord-right1">
        <div class="orderRecord-right-header-container">
            <div class="orderRecord-right-header-btn active" id="all-btn" data-target=".orderRecord-right-all">
                All
            </div>
            <div class="orderRecord-right-header-btn" id="toship-btn" data-target=".orderRecord-right-toship">
                To Ship
            </div>
            <div class="orderRecord-right-header-btn" id="toreceive-btn" data-target=".orderRecord-right-toreceive">
                To Receive
            </div>
            <div class="orderRecord-right-header-btn" id="completed-btn" data-target=".orderRecord-right-completed">
                Completed
            </div>
        </div>

        <div class="orderRecord-right-content">
            <div class="orderRecord-right-all">
                <?php
                if (count($orderAll_arr) == 0) {
                ?>
                    <div class="orderRecord-right-noOrdersYet">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <div>No Orders Yet</div>
                    </div>
                <?php }
                foreach ($orderAll_arr as $arrAll): ?>
                    <div class="orderRecord-right-table">
                        <table>
                            <tr class="order_status_row">
                                <td colspan="3" class="order_status_col"><?= $arrAll->order_status ?></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <hr>
                                </td>
                            </tr>
                            <?php
                            $stm = $_db->prepare(
                                'SELECT *
                            FROM orderitem
                            INNER JOIN product
                            on orderitem.product_id = product.product_id
                            INNER JOIN product_photo
                            on product_photo.product_id = product.product_id
                            where orderitem.order_id = ?'
                            );
                            $stm->execute([$arrAll->order_id]);
                            $orderItem_arr = $stm->fetchAll();

                            foreach ($orderItem_arr as $item_arr):
                            ?>
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
                            <tr class="order_total_row">
                                <td colspan="3" class="order_total_col">
                                    Order Total: <span style="font-size: 22px;">RM <?= $arrAll->total_amount ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="orderRecord-right-toship" style="display: none;">
                <?php
                if (count($orderShip_arr) == 0) {
                ?>
                    <div class="orderRecord-right-noOrdersYet">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <div>No Orders Yet</div>
                    </div>
                <?php }
                foreach ($orderShip_arr as $arrShip): ?>
                    <form method="POST" action="get_order_details.php">
                        <input type="hidden" name="orderId" value="<?= $arrShip->order_id ?>">
                        <input type="hidden" name="headerbtn-option" value="toship">
                        <div class="orderRecord-right-table">
                            <table>
                                <tr class="order_status_row">
                                    <td colspan="3" class="order_status_col"><?= $arrShip->order_status ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <hr>
                                    </td>
                                </tr>
                                <?php
                                $stm = $_db->prepare(
                                    'SELECT *
                            FROM orderitem
                            INNER JOIN product
                            on orderitem.product_id = product.product_id
                            INNER JOIN product_photo
                            on product_photo.product_id = product.product_id
                            where orderitem.order_id = ?'
                                );
                                $stm->execute([$arrShip->order_id]);
                                $orderItem_arr = $stm->fetchAll();

                                foreach ($orderItem_arr as $item_arr):
                                ?>
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
                                <tr class="order_total_row">
                                    <td colspan="3" class="order_total_col">
                                        Order Total: <span style="font-size: 22px;">RM <?= $arrShip->total_amount ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <?php
                                    $stm = $_db->prepare(
                                        'SELECT DATE(DATE_ADD(order_date, INTERVAL 3 DAY)) AS updated_date
                                    FROM order_record
                                    WHERE order_id = ?'
                                    );
                                    $stm->execute([$arrShip->order_id]);
                                    $arrivedDate = $stm->fetch();
                                    ?>
                                    <td class="ship_msg" colspan="2">These products are expected to arrive on <?= $arrivedDate->updated_date ?></td>
                                    <td class="check_details"><button type="submit" class="check_details-btn">Order Details</button></td>
                                </tr>
                            </table>
                        </div>
                    </form>
                <?php endforeach ?>
            </div>

            <div class="orderRecord-right-toreceive" style="display: none;">
                <?php
                if (count($orderReceive_arr) == 0) {
                ?>
                    <div class="orderRecord-right-noOrdersYet">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <div>No Orders Yet</div>
                    </div>
                <?php
                }
                foreach ($orderReceive_arr as $arrReceive):
                ?>
                    <form method="POST" action="get_order_details.php">
                        <input type="hidden" name="orderId" value="<?= $arrReceive->order_id ?>">
                        <input type="hidden" name="headerbtn-option" value="toreceive">
                        <div class="orderRecord-right-table">
                            <table>
                                <tr class="order_status_row">
                                    <td colspan="3" class="order_status_col"><?= $arrReceive->order_status ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <hr>
                                    </td>
                                </tr>
                                <?php
                                $stm = $_db->prepare(
                                    'SELECT *
                            FROM orderitem
                            INNER JOIN product
                            on orderitem.product_id = product.product_id
                            INNER JOIN product_photo
                            on product_photo.product_id = product.product_id
                            where orderitem.order_id = ?'
                                );
                                $stm->execute([$arrReceive->order_id]);
                                $orderItem_arr = $stm->fetchAll();

                                foreach ($orderItem_arr as $item_arr):
                                ?>
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
                                <tr class="order_total_row">
                                    <td colspan="3" class="order_total_col">
                                        Order Total: <span style="font-size: 22px;">RM <?= $arrReceive->total_amount ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="confirm_msg">Confirm receipt after you've checked the received items</td>
                                    <td class="order_received"><button type="submit" class="order_received-btn" data-order-id="<?= $arrReceive->order_id ?>">Order Received</button></td>
                                    <td class="check_details"><button type="submit" class="check_details-btn">Order Details</button></td>
                                </tr>
                            </table>
                        </div>
                    </form>
                <?php endforeach ?>
            </div>

            <div class="orderRecord-right-completed" style="display: none;">
                <?php
                if (count($orderCompleted_arr) == 0) {
                ?>
                    <div class="orderRecord-right-noOrdersYet">
                        <i class="fa-regular fa-pen-to-square"></i>
                        <div>No Orders Yet</div>
                    </div>
                <?php }
                foreach ($orderCompleted_arr as $arrCompleted): ?>
                    <form method="POST" action="get_order_details.php">
                        <input type="hidden" name="orderId" value="<?= $arrCompleted->order_id ?>">
                        <input type="hidden" name="headerbtn-option" value="completed">
                        <div class="orderRecord-right-table">
                            <table>
                                <tr class="order_status_row">
                                    <td colspan="3" class="order_status_col"><?= $arrCompleted->order_status ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <hr>
                                    </td>
                                </tr>
                                <?php
                                $stm = $_db->prepare(
                                    'SELECT *
                            FROM orderitem
                            INNER JOIN product
                            on orderitem.product_id = product.product_id
                            INNER JOIN product_photo
                            on product_photo.product_id = product.product_id
                            where orderitem.order_id = ?'
                                );
                                $stm->execute([$arrCompleted->order_id]);
                                $orderItem_arr = $stm->fetchAll();

                                foreach ($orderItem_arr as $item_arr):
                                ?>
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
                                <tr class="order_total_row">
                                    <td colspan="3" class="order_total_col">
                                        Order Total: <span style="font-size: 22px;">RM <?= $arrCompleted->total_amount ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <?php
                                    $stm = $_db->prepare(
                                        'SELECT *
                                            FROM order_record
                                            INNER JOIN orderitem
                                            on order_record.order_id = orderitem.order_id
                                            INNER JOIN item_rating
                                            on orderitem.orderItem_id = item_rating.orderItem_id
                                            WHERE order_record.order_id = ?'
                                    );
                                    $stm->execute([$arrCompleted->order_id]);
                                    $ratingItem_arr = $stm->fetchAll();


                                    if (count($ratingItem_arr) == 0) {
                                    ?>
                                        <td class="order_rating" colspan="2"><button type="button" class="order_rating-btn" data-order-id="<?= $arrCompleted->order_id ?>">Rate</button></td>
                                    <?php } else { ?>
                                        <td colspan="2"></td>
                                    <?php } ?>
                                    <td class="check_details"><button type="submit" class="check_details-btn">Order Details</button></td>
                                </tr>
                            </table>
                        </div>
                    </form>

                    <div class="order-rating-modal" id="order-rating-modal-<?= $arrCompleted->order_id ?>">
                        <form action="rating.php" method="POST">
                            <table class="order-rating-table">
                                <tr>
                                    <td class="order-rating-table-title">Rate Product</td>
                                </tr>
                                <?php
                                $stm = $_db->prepare(
                                    'SELECT *
                                    FROM orderitem
                                    INNER JOIN product
                                    on orderitem.product_id = product.product_id
                                    INNER JOIN product_photo
                                    on product_photo.product_id = product.product_id
                                    where orderitem.order_id = ?'
                                );
                                $stm->execute([$arrCompleted->order_id]);
                                $orderItem_arr = $stm->fetchAll();

                                foreach ($orderItem_arr as $item_arr):
                                ?>
                                    <input type="hidden" name="rate_items_id[]" value="<?= $item_arr->orderItem_id ?>">
                                    <tr>
                                        <td>
                                            <hr>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="order-rating-table-product">
                                            <label class="rate-product-photo"><img src="/photos/<?= $item_arr->photo ?>"></label>
                                            <?= $item_arr->description ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="order-rating-table-rate">
                                            <div class="order-rating-table-rate-text">
                                                Product Quality
                                            </div>
                                            <div class="order-rating-table-rate-star">
                                                <button type="button" class="star" data-value="1" data-item-id="<?= $item_arr->orderItem_id ?>"><i class=" fa-solid fa-star star"></i></button>
                                                <button type="button" class="star" data-value="2" data-item-id="<?= $item_arr->orderItem_id ?>"><i class=" fa-solid fa-star star"></i></button>
                                                <button type="button" class="star" data-value="3" data-item-id="<?= $item_arr->orderItem_id ?>"><i class=" fa-solid fa-star star"></i></button>
                                                <button type="button" class="star" data-value="4" data-item-id="<?= $item_arr->orderItem_id ?>"><i class=" fa-solid fa-star star"></i></button>
                                                <button type="button" class="star" data-value="5" data-item-id="<?= $item_arr->orderItem_id ?>"><i class=" fa-solid fa-star star"></i></button>
                                            </div>
                                            <div class="order-rating-table-rate-word" data-item-id="<?= $item_arr->orderItem_id ?>">
                                                Amazing
                                            </div>
                                            <input type="hidden" name="rate_words[]" id="rate-word-<?= $item_arr->orderItem_id ?>" value="Amazing">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="order-rating-table-comment">
                                            <label for="comment">Comment:</label>
                                            <textarea id="comment" name="comment[]" rows="4" cols="66"></textarea>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </table>
                            <div class="order-rating-table-action">
                                <button type="button" class="order-rating-modal-cancel-btn">Cancel</button>
                                <button type="submit" class="order-rating-modal-confirm-btn">Confirm</button>
                            </div>
                        </form>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
    <div id="order-rating-overlay"></div>

    <div class="orderRecord-right2" style="display: none;">
        <div class="orderRecord-voucher-title">
            Vouchers / Discounts
        </div>
        <hr>
        <?php
        if (count($voucher_arr) != 0) {
        ?>
            <div class="orderRecord-member-voucher-wrapper">
                <?php foreach ($voucher_arr as $voucher): ?>
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
                                <a href="menu.php" class="orderRecord-voucher-shop-btn">Shop<br>Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php } else { ?>
            <div class="orderRecord-member-voucher-wrapper">
                <div class="orderRecord-no-voucher-wrapper">
                    <div class="orderRecord-no-voucher">
                        <i class="fa-regular fa-pen-to-square"></i><br>
                        <div class="orderRecord-no-voucher-text">No Available Vouchers</div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php
include '_foot.php';
