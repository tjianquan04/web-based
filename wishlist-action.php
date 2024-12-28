<?php
require '_base.php';
header('Content-Type: application/json');

// Fetch and validate input
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['action'], $data['product_id']) || empty($data['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$action = $data['action'];
$product_id = $data['product_id'];

if(!empty($_SESSION)){
    $member = $_SESSION['user'];
    authMember($member);
  
  $member_id =  $member->member_id;
  
  }
try {
    if ($action === 'add') {
        // Add product to wishlist
        $stm = $_db->prepare('INSERT INTO wishlist (member_id, product_id) VALUES (?, ?)');
        $stm->execute([$member_id, $product_id]);
        echo json_encode(['success' => true, 'message' => 'Item added to wishlist.']);
    } elseif ($action === 'remove') {
        // Remove product from wishlist
        $stm = $_db->prepare('DELETE FROM wishlist WHERE member_id = ? AND product_id = ?');
        $result = $stm->execute([$member_id, $product_id]);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Item removed from wishlist.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item from wishlist.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>

