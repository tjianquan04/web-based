<?php
require '_base.php';

$productId = $_POST['productId'];
$qty = $_POST['qty'];
$member = $_SESSION['user'];
authMember($member);
$id =  $member->member_id;

$stm = $_db->prepare(
    'SELECT *
    FROM cartitem
    WHERE product_id = ?
    AND member_id = ?'
);
$stm->execute([$productId, $id]);
$result = $stm->fetchAll();

if (count($result) == 0) {
    $stm1 = $_db->prepare(
        'SELECT cartItem_id
    FROM cartitem
    ORDER BY cartItem_id DESC
    LIMIT 1'
    );
    $stm1->execute();
    $lastCartItemId = $stm1->fetchColumn();

    if ($lastCartItemId) {
        $outputInt = (intval(substr($lastCartItemId, 2))) + 1;
        $newCartItemId = 'CA' . str_pad($outputInt, 4, '0', STR_PAD_LEFT);
    } else {
        $newCartItemId = 'CA0001';
    }

    $stm2 = $_db->prepare(
        'INSERT INTO `cartitem`(`cartItem_id`, `quantity`, `member_id`, `product_id`)
    VALUES (?,?,?,?)'
    );
    $stm2->execute([$newCartItemId, $qty, $id, $productId]);
} else {
    $stm3 = $_db->prepare(
        'UPDATE cartitem
        SET quantity = quantity + ?
        WHERE product_id = ?
        AND member_id = ?'
    );
    $stm3->execute([$qty,$productId,$id]);
}
// if (is_post()) {
//     $member_id = "M000001";
//     $productId = $_POST['productId'];
//     $qty = $_POST['qty'];
    
//     $stm = $_db->prepare(
//         'SELECT * 
//         FROM cartitem
//         WHERE product_id = ?
//         AND member_id = ?'
//     );
//     $stm->execute([$productId, $member_id]);
//     $result = $stm->fetch();

//     if(count($result) != 0){
//         $stm = $_db->prepare(
//             'UPDATE cartitem
//             SET quantity = 5
//             WHERE product_id = ?
//             AND member_id = ?'
//         );
//         $stm->execute([$productId, $member_id]);
//     }else{
//         $stm3 = $_db->prepare(
//             'SELECT cartItem_id
//             FROM cartitem
//             ORDER BY cartItem_id DESC
//             LIMIT 1'
//         );
//         $stm3->execute();
//         $lastCartItemId = $stm3->fetchColumn();

//         if ($lastCartItemId) {
//             $outputInt = (intval(substr($lastCartItemId, 2))) + 1;
//             $newCartItemId = 'CA' . str_pad($outputInt, 4, '0', STR_PAD_LEFT);
//         } else {
//             $newCartItemId = 'CA0001';
//         }

//         $stm4 = $_db->prepare(
//             'INSERT INTO `cartitem`(`cartItem_id`, `quantity`, `member_id`, `product_id`) 
//             VALUES (?,?,?,?)'
//         );
//         $stm4->execute([$newCartItemId,$qty,$member_id, $productId]);
//     }

//     if ($result) {
//         echo json_encode(['success' => true]);
//     } else {
//         echo json_encode(['error' => 'Failed to add to cart.']);
//     }
//     exit;
// }
