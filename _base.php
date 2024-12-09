<?php

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
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

function validateAdmin($admin_id, $password)
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Prepare a statement to fetch the admin by admin_id
        $stm = $_db->prepare("SELECT * FROM `admin` WHERE admin_id = ?");
        $stm->execute([$admin_id]);
        $admin = $stm->fetch(); // Fetch the admin record
        // Check if admin exists and password matches
        if ($admin && password_verify($password, $admin->password)) {
            // Return the admin object with role information if credentials are valid
            return $admin;
        } else {
            return false; // Return false if credentials are invalid
        }
    } catch (PDOException $e) {
        // Log the error or handle it
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

function getAllAdmins()
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Prepare the SQL query to fetch all admins excluding the superadmin and the current logged-in admin
        $stmt = $_db->prepare("SELECT * FROM `admin` WHERE `role` != 'superadmin'");
        $stmt->execute();
        
        // Fetch all the results as an associative array
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Log the error and return an empty array or handle as needed
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

//auto genetate next user id 
function getNextUserId() {
    global $_db;
    
    // get the highest member_id 
    $stmt = $_db->query("SELECT MAX(member_id) AS max_id FROM member");
    $row = $stmt->fetch();
    
    // Get the current highest member_id 
    $max_id = $row->max_id;
    
    // If no records, return M000001
    if ($max_id === null) {
        return 'M000001';
    }
    
    // Extract the numeric part of the current max_id 
    $numeric_part = (int) substr($max_id, 1);
    
    // Increment the numeric part and pad it to 6 digits
    $new_id = 'M' . str_pad($numeric_part + 1, 6, '0', STR_PAD_LEFT);
    
    return $new_id;
}

//auto generate random username
function generateRandomUsername() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
    $name = '';
    
    // Generate a random username of 8 characters
    for ($i = 0; $i < 8; $i++) {
        $name .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $name;
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

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

function get_file($key)
{
    $f = $_FILES[$key] ?? null;

    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

function html_file($key, $value = '', $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' value= '$value' accept='$accept' $attr>";
}

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}

// Generate input field
function html_input($type, $key, $placeholder = '', $data = '', $attr = '') {
    $value = htmlspecialchars($data);
    $placeholder = encode($placeholder);
    echo "<input type='$type' id='$key' name='$key' value='$value' placeholder='$placeholder' $attr>";
}

// Generate text input field
function html_text($key, $placeholder = '', $data = '', $attr = '') {
    html_input('text', $key, $placeholder, $data, $attr);
}

// Generate password input field
function html_password($key, $placeholder = '', $data = '', $attr = '') {
    html_input('password', $key, $placeholder, $data, $attr);
}

// Generate email input field
function html_email($key, $placeholder = '', $data = '', $attr = '') {
    html_input('email', $key, $placeholder, $data, $attr);
}

// Generate search input field
function html_search($key,$placeholder = 'Search by name, email, contact', $data = "", $attr = '') {
    html_input('search', $key, $placeholder, $data, $attr);
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



