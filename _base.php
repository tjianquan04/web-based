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
        if ($admin && $admin->password === sha1($password)) {
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

function addAdmin($admin_name, $adminEmail, $adminPassword)
{
    global $_db;

    // Check if the admin email already exists
    $stm = $_db->prepare("SELECT * FROM `admin` WHERE email = ?");
    $stm->execute([$adminEmail]);
    
    // If email already exists, return false
    if ($stm->rowCount() > 0) {
        return false; // Email already exists
    }

    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    // Default values for other fields
    $admin_id = generateNextAdminId();
    $role = 'Admin';  // Set default role as 'admin'
    $phone_number = '-';  // Similarly, handle phone number if needed
    $status = 'Active';
    
    try {
        // Prepare SQL query to insert new admin
        $stmt = $_db->prepare("INSERT INTO admin (admin_id, admin_name, password, role, email, phone_number, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Execute the statement with the form data
        $result = $stmt->execute([$admin_id, $admin_name, $hashedPassword, $role, $adminEmail, $phone_number, $status]);
        return $result;

    } catch (PDOException $e) {
        // Handle the error if something goes wrong with the query
        echo "Database error: " . $e->getMessage();
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

function generateNextAdminId()
{
    global $_db;

    try {
        // Fetch the latest admin_id from the database
        $stm = $_db->query("SELECT MAX(admin_id) AS latest_id FROM admin");
        $latestAdmin = $stm->fetch(PDO::FETCH_OBJ);

        // If there is an existing admin, increment the admin_id
        if ($latestAdmin && isset($latestAdmin->latest_id)) {
            $nextId = $latestAdmin->latest_id + 1;  // Increment the latest ID by 1
        } else {
            // If no admins exist, start from 1000
            $nextId = 1000;
        }

        return $nextId;

    } catch (PDOException $e) {
        // Handle database errors
        error_log("Error generating next admin ID: " . $e->getMessage());
        return false; // Indicate failure
    }
}

// Function to get admin details by admin_id
function getAdminById($admin_id) {
    global $_db; // Assuming you're using PDO for database interaction

    // Prepare the SQL query to fetch the admin details by admin_id
    $stmt = $_db->prepare('SELECT * FROM admin WHERE admin_id = ? LIMIT 1');
    
    // Execute the query with the given admin_id
    $stmt->execute([$admin_id]);
    
    // Fetch the result as an object
    $admin = $stmt->fetch(PDO::FETCH_OBJ);
    
    // Return the admin object or null if not found
    return $admin ?: null;
}


function generateDynamicPagination($pager)
{
    $currentPage = $pager->page;
    $totalPages = $pager->page_count;

    $paginationHTML = '<div class="pagination">';

    // Add "First" link
    if ($currentPage > 1) {
        $paginationHTML .= '<a href="?page=1">&laquo; First</a>';
    }

    // Add "Previous" link
    if ($currentPage > 1) {
        $paginationHTML .= '<a href="?page=' . ($currentPage - 1) . '">&lt;</a>';
    }

    // Show previous, current, and next pages
    for ($i = max(1, $currentPage - 1); $i <= min($totalPages, $currentPage + 1); $i++) {
        if ($i == $currentPage) {
            $paginationHTML .= '<span class="active">' . $i . '</span>';
        } else {
            $paginationHTML .= '<a href="?page=' . $i . '">' . $i . '</a>';
        }
    }

    // Add "Next" link
    if ($currentPage < $totalPages) {
        $paginationHTML .= '<a href="?page=' . ($currentPage + 1) . '">&gt;</a>';
    }

    // Add "Last" link
    if ($currentPage < $totalPages) {
        $paginationHTML .= '<a href="?page=' . $totalPages . '">Last &raquo;</a>';
    }

    $paginationHTML .= '</div>';

    return $paginationHTML;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("../image/$photo", 'image/jpeg');

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

function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
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



//Product
function fetchProducts($db, $category, $category_id, $name, $sort, $dir) {
    $query = "
        SELECT p.*, pp.photo
        FROM product p
        LEFT JOIN product_photo pp 
        ON p.product_id = pp.product_id AND pp.default_photo = 1
        WHERE 1=1
    ";

    $params = [];
    
    if ($category) {
        $query .= " AND p.category_name = ?";
        $params[] = $category;
    }
    if ($category_id) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    if ($name) {
        $query .= " AND p.description LIKE ?";
        $params[] = '%' . $name . '%';
    }

    $query .= " ORDER BY $sort $dir";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


