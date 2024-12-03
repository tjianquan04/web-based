<?php
// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

//check user exist in the database
function validateUser($email, $password)
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Prepare a statement to fetch the user by email
        $stm = $_db->prepare("SELECT * FROM `user` WHERE email = ?");
        $stm->execute([$email]);
        $user = $stm->fetch();

        // Check if user exists and password matches
        if ($user && password_verify($password, $user->password)) {
            return $user; // Return the user object if credentials are valid
        } else {
            return false; // Return false if credentials are invalid
        }
    } catch (PDOException $e) {
        // Log the error or handle it
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

function getNextUserId() {
    global $_db;
    
    // Query to get the highest user_id in the database
    $stmt = $_db->query("SELECT MAX(user_id) AS max_id FROM user");
    $row = $stmt->fetch();
    
    // Return the next user_id (max_id + 1)
    return $row->max_id + 1;
}

// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

//connect database
$_db = new PDO('mysql:dbname=assignment_db', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

