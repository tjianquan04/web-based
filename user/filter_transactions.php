<?php
require '../_base.php';

$type = $_POST['type'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$member = $_SESSION['user'] ?? ''; // Get the member_id from session

if (!$member->member_id) {
    // Handle the case where member_id is not found in the session (e.g., redirect or error)
    echo json_encode(['error' => 'Member ID is missing']);
    exit;
}
$query = "SELECT * FROM transactions WHERE member_id = ?";
$params = [$member->member_id]; // Filter transactions for the specific member

// Filter by type
if ($type) {
    $query .= " AND trans_type = ?";
    $params[] = $type;
}

if ($month && $year) {
    $startDate = "$year-$month-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month
    $query .= " AND trans_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
} elseif ($year) {
    $query .= " AND YEAR(trans_date) = ?";
    $params[] = $year;
} elseif ($month) {
    // If only month is given, assume the current year
    $currentYear = date("Y");
    $startDate = "$currentYear-$month-01";
    $endDate = date("Y-m-t", strtotime($startDate));
    $query .= " AND trans_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

$stmt = $_db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_OBJ);

// Return the transactions as JSON
header('Content-Type: application/json');
echo json_encode($transactions);
?>
