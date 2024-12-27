<?php
require '_base.php';

if (is_post()) {
    $rateItemIds = $_POST['rate_items_id'];
    $starWords = $_POST['rate_words'];
    $comments = $_POST['comment'];

    foreach ($rateItemIds as $index => $itemId) {
        $starWord = $starWords[$index];
        $comment = $comments[$index]; 

        $stm = $_db->prepare(
            'SELECT rating_id
            FROM item_rating
            ORDER BY rating_id DESC
            LIMIT 1'
        );
        $stm->execute();
        $lastRatingId = $stm->fetchColumn();

        if ($lastRatingId) {
            $outputInt = (intval(substr($lastRatingId, 1))) + 1;
            $newRatingId = 'S' . str_pad($outputInt, 5, '0', STR_PAD_LEFT);
        } else {
            $newRatingId = 'S00001';
        }

        $stm2 = $_db->prepare(
            'INSERT INTO `item_rating`(`rating_id`, `rating_star`, `comment`, `orderItem_id`)
        VALUES (?,?,?,?)'
        );
        $stm2->execute([$newRatingId, $starWord, $comment, $itemId]);
    }

    header('Location: order_record.php?section=.orderRecord-right-completed');
    exit;
}
