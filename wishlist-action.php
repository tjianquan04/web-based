<?php
require '_base.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['product_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}



$product_id = $data['product_id'];
$action = $data['action'];

// Assuming you have a `wishlist` table with columns: `user_id`, `product_id`
$member_id = "M000001"; // Get user_id from session

// Debugging: Check if product_id and action are set properly
error_log("Product ID: $product_id, Action: $action, User ID: $member_id");

try {
    if ($action === 'add') {
        // Add the product to the wishlist
        $stm = $_db->prepare('INSERT INTO wishlist (member_id, product_id) VALUES (?, ?)');
        $stm->execute([$member_id, $product_id]);
    } elseif ($action === 'remove') {
        // Remove the product from the wishlist
        $stm = $_db->prepare('DELETE FROM wishlist WHERE member_id = ? AND product_id = ?');
        $stm->execute([$member_id, $product_id]);
    }

    // If action was successful
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Log any database errors and return a message
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Catch any other exceptions
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
