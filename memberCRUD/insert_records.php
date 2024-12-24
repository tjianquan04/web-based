<?php
include('../_base.php');

//auth('Admin');

// Prepare the SQL statement for checking if the member_id exists
$checkStmt = $_db->prepare('SELECT COUNT(*) FROM member WHERE member_id = ?');

// Prepare the SQL statement for inserting member records
$insertStmt = $_db->prepare('
    INSERT INTO member (
        member_id, name, email, contact, password, wallet, register_date, 
        login_attempts, status, profile_photo, last_email_sent
    ) VALUES (?, ?, ?, ?, SHA1(?), ?, ?, ?, ?, ?, ?)
');
$n = 0;


$f = fopen('../member.csv', 'r');
if ($f !== false) {


    while ($data = fgetcsv($f)) {

        if (empty(array_filter($data))) {
            break; 
        }

        if (count($data) == 11) {
            $member_id = $data[0]; 

            // Check if the member_id already exists in the database
            $checkStmt->execute([$member_id]);
            $exists = $checkStmt->fetchColumn() > 0;

            if (!$exists) {
                $n += $insertStmt->execute($data);
            } else {
                error_log("Member ID $member_id already exists, skipping insert.");
            }
        } else {
            error_log("Invalid row: " . implode(', ', $data)); 
        }
    }
    fclose($f);
} else {
    // Handle error if the file cannot be opened
    temp('error', 'Unable to open member.csv file.');
}

// Output the result
temp('info', "$n record(s) inserted.");

redirect('../admin/member_management.php');
?>

