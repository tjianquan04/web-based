<?php

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
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
// ============================================================================
// HTML Helpers
// ============================================================================

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}

// Generate input field
function html_input($type, $key, $placeholder = '', $data = [], $attr = '') {
    $value = encode($data[$key] ?? '');
    $placeholder = encode($placeholder);
    echo "<input type='$type' id='$key' name='$key' value='$value' placeholder='$placeholder' $attr>";
}

// Generate text input field
function html_text($key, $placeholder = '', $data = [], $attr = '') {
    html_input('text', $key, $placeholder, $data, $attr);
}

// Generate password input field
function html_password($key, $placeholder = '', $data = [], $attr = '') {
    html_input('password', $key, $placeholder, $data, $attr);
    echo "<input type='checkbox' id='show-password' onclick='togglePasswordVisibility()'> Show Password<br>";
}

// Generate email input field
function html_email($key, $placeholder = '', $data = [], $attr = '') {
    html_input('email', $key, $placeholder, $data, $attr);
}


// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {

    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='error-message'>$_err[$key]</span>";
    }
    else {
        echo '<span class = "error-message"></span>';
    }
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

//Connect Database
$_db = new PDO('mysql:dbname=assignment_db', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}



