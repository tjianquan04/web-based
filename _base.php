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
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
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


function getNextUserId()
{
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

function getNextAddressId()
{
    global $_db;
    
    // get the highest member_id 
    $stmt = $_db->query("SELECT MAX(address_id) AS max_id FROM address");
    $row = $stmt->fetch();
    
    // Get the current highest member_id 
    $max_id = $row->max_id;
    
    // If no records, return M000001
    if ($max_id === null) {
        return 'A000001';
    }
    
    // Extract the numeric part of the current max_id 
    $numeric_part = (int) substr($max_id, 1);
    
    // Increment the numeric part and pad it to 6 digits
    $new_id = 'A' . str_pad($numeric_part + 1, 6, '0', STR_PAD_LEFT);
    
    return $new_id;
}

function getMemberbyId($member_id){
    global $_db;
    
    $stmt = $_db->prepare("SELECT * FROM member where member_id = ? LIMIT 1");

    $stmt->execute([$member_id]);

    $member = $stmt->fetch(PDO::FETCH_OBJ);
   
    return $member ?: null;
}

function getAllAddressbyId($memberId){
    global $_db;
    $addressStm = $_db->prepare('SELECT * FROM address WHERE member_id = ?');
    $addressStm->execute([$memberId]);
    $addressArr = $addressStm->fetchAll();

    return $addressArr;
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
function getAdminById($admin_id)
{
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


function generateDynamicPagination($pager, $sort, $dir)
{
    $currentPage = $pager->page;
    $totalPages = $pager->page_count;

    // Start the pagination container
    $paginationHTML = '<div class="pagination">';

    // Add "First" link with sort and dir parameters
    if ($currentPage > 1) {
        $paginationHTML .= '<a href="?page=1&sort=' . $sort . '&dir=' . $dir . '">&laquo; First</a>';
    }

    // Add "Previous" link with sort and dir parameters
    if ($currentPage > 1) {
        $paginationHTML .= '<a href="?page=' . ($currentPage - 1) . '&sort=' . $sort . '&dir=' . $dir . '">&lt;</a>';
    }

    // Show previous, current, and next pages, with sort and dir parameters
    for ($i = max(1, $currentPage - 1); $i <= min($totalPages, $currentPage + 1); $i++) {
        if ($i == $currentPage) {
            // Highlight the current page
            $paginationHTML .= '<span class="active">' . $i . '</span>';
        } else {
            // Link to other pages, with sort and dir parameters
            $paginationHTML .= '<a href="?page=' . $i . '&sort=' . $sort . '&dir=' . $dir . '">' . $i . '</a>';
        }
    }

    // Add "Next" link with sort and dir parameters
    if ($currentPage < $totalPages) {
        $paginationHTML .= '<a href="?page=' . ($currentPage + 1) . '&sort=' . $sort . '&dir=' . $dir . '">&gt;</a>';
    }

    // Add "Last" link with sort and dir parameters
    if ($currentPage < $totalPages) {
        $paginationHTML .= '<a href="?page=' . $totalPages . '&sort=' . $sort . '&dir=' . $dir . '">Last &raquo;</a>';
    }

    // Close the pagination container
    $paginationHTML .= '</div>';

    return $paginationHTML;
}


$_user = $_SESSION['user'] ?? null;

function login($user, $url = '/')
{
    $_SESSION['user'] = $user;
    
    redirect($url);
}

// Logout user
function logout($url = '/')
{
    unset($_SESSION['user']);
    redirect($url);
}

// Authorization
function auth(...$roles)
{
    global $_user;  // Assuming $_user is set when the admin logs in

    if ($_user) {
        // Check if the user's role is valid
        if (in_array($_user->role, $roles)) {
            // Check if the user's account is active
            if ($_user->status === 'Active') {
                $_err = 'Your account is inactive. Please contact support.';
                return;  // User is authorized, continue execution
            } else {
                redirect('admin_login.php');
            }
        } else {
            // If the user's role is not in the allowed roles, redirect to login
            redirect('admin_login.php');
        }
    }

    redirect('admin_login.php');
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        // TODO
        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// Is unique?
function is_unique($value, $table, $field)
{
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
function encode($value)
{
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

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
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
function err($key)
{

    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='error-message'>$_err[$key]</span>";
    } else {
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
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Is money?
function is_money($value) {
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
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


function fetchProductsWithPhotos($db, $category, $category_id, $name, $sort = 'description', $dir = 'asc') {
    $query = "
        SELECT p.*, pp.photo 
        FROM product p
        LEFT JOIN product_photo pp ON p.product_id = pp.product_id AND pp.default_photo = 1
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
        $params[] = "%$name%";
    }

    $query .= " ORDER BY $sort $dir";

    $stm = $db->prepare($query);
    $stm->execute($params);

    return $stm->fetchAll();
}

function html_select_with_subcategories($key, $categories, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    
    // Default option
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }

    foreach ($categories as $main_category => $data) {
        $id = $data['id'];
        $has_subcategories = !empty($data['subcategories']);
        
        // Main category: Disable if it has subcategories
        $disabled = $has_subcategories ? 'disabled' : '';
        echo "<option value='$id' $disabled>$main_category</option>";

        // Subcategories
        if ($has_subcategories) {
            foreach ($data['subcategories'] as $subcategory) {
                $sub_id = $subcategory['id'];
                $sub_name = $subcategory['name'];
                $selected = $sub_id == $value ? 'selected' : '';
                echo "<option value='$sub_id' $selected>&nbsp;&nbsp;&nbsp;$sub_name</option>";
            }
        }
    }

    echo '</select>';
}

function generate_product_id($category_name, $subcategory, $_db) {
    // Define mnemonics for categories and subcategories
    $mnemonics = [
        'racquet' => '1',
        'shuttlecock' => '2SC',
        'racquetbag' => '3RB',
    ];

    $sub_mnemonics = [
        'xpseries' => 'XP',
        '3dcalibar' => '3D',
        'axforce' => 'AX',
        'tectonic' => 'TT',
    ];

    $prefix = $mnemonics[strtolower($category_name)] ?? '';
    $sub_prefix = $sub_mnemonics[strtolower($subcategory)] ?? '';

    if ($prefix && $sub_prefix) {
        $id_prefix = $prefix . $sub_prefix;
    } else {
        $id_prefix = $prefix; // Use main category mnemonic if subcategory doesn't exist
    }

    // Fetch the next sequence number
    $stm = $_db->prepare("SELECT COUNT(*) + 1 AS seq FROM product WHERE product_id LIKE ?");
    $stm->execute(["$id_prefix%"]);
    $row = $stm->fetch();
    $seq = str_pad($row['seq'], 4, '0', STR_PAD_LEFT);

    return $id_prefix . '-' . $seq;
}

