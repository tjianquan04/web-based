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
        if ($user && $user->password === sha1($password)) {
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

function validUserEmail($email)
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Prepare the SQL query to check if the email exists
        $stmt = $_db->prepare("SELECT * FROM `user` WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(); // Fetch the admin record

        if ($user) {
            return $user;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        // Log the error and handle gracefully
        error_log("Database error in checkValidEmail: " . $e->getMessage());
        return false; // Treat failure as email not existing
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

function validAdminEmail($email)
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Prepare the SQL query to check if the email exists
        $stmt = $_db->prepare("SELECT * FROM `admin` WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(); // Fetch the admin record

        if ($admin) {
            return $admin;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        // Log the error and handle gracefully
        error_log("Database error in checkValidEmail: " . $e->getMessage());
        return false; // Treat failure as email not existing
    }
}

function updateSessionData($admin_id)
{
    global $_db;

    $stm = $_db->prepare('SELECT * FROM admin WHERE admin_id = ?');
    $stm->execute([$admin_id]);
    $user = $stm->fetchObject();

    if ($user) {
        $_SESSION['user'] = $user;
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

function addAdmin($user)
{
    global $_db;

    // Check if the admin email already exists
    $stm = $_db->prepare("SELECT * FROM `admin` WHERE email = ?");
    $stm->execute([$user->email]);

    if ($stm->rowCount() > 0) {
        return false; // Email already exists
    }

    // Hash the password using SHA1 (or consider using password_hash())
    $hashed_password = sha1($user->password);

    // Default values for other fields
    $admin_id = generateNextAdminId();
    $phone_number = $user->phone_number ?? '-'; // Default to '-' if no phone number is provided

    // Handle photo upload
    if ($user->photo && str_starts_with($user->photo->type, 'image/')) {
        $photo_path = save_photo($user->photo, '../photos'); // Save photo in 'photos' folder
    } else {
        $photo_path = 'default_user_photo.png'; // Default photo if none is uploaded
    }

    // Insert the new admin into the database
    try {
        $stmt = $_db->prepare("INSERT INTO admin (admin_id, admin_name, password, role, email, phone_number, status, photo) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Execute the statement with the form data
        return $stmt->execute([
            $admin_id,
            $user->admin_name,
            $hashed_password,
            $user->role,
            $user->email,
            $phone_number,
            $user->status,
            $photo_path
        ]);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return false;
    }
}

function validCurrentPassword($password, $member_id){
    global $_db;
    
    //get current password from database
    $stmt = $_db->prepare("SELECT password FROM member WHERE member_id = ?");
    $stmt ->execute($member_id);
    $current_password = $stmt ->fetch();
    
    if(SHA1($password) != $current_password){
        return false;
    }
    return true;
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

function getMemberbyId($member_id)
{
    global $_db;

    $stmt = $_db->prepare("SELECT * FROM member where member_id = ? LIMIT 1");

    $stmt->execute([$member_id]);

    $member = $stmt->fetch(PDO::FETCH_OBJ);

    return $member ?: null;
}

function getAllAddressbyMemberId($memberId)
{
    global $_db;
    $addressStm = $_db->prepare('SELECT * FROM address WHERE member_id = ?');
    $addressStm->execute([$memberId]);
    $addressArr = $addressStm->fetchAll();

    return $addressArr;
}

function getAddressbyId($address_id)
{
    global $_db;
    $addressStm = $_db->prepare('SELECT * FROM address WHERE address_id = ?');
    $addressStm->execute([$address_id]);
    $address = $addressStm->fetch(PDO::FETCH_OBJ);

    return $address;
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

function getTotalSales()
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Query to calculate the total amount
        $stmt = $_db->query("SELECT SUM(total_amount) AS total FROM `order_record`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch a single row as an associative array

        // Check and return the total
        if ($result && isset($result['total'])) {
            return (float)$result['total']; // Cast to float for consistent numeric output
        } else {
            return 0.0; // Return 0.0 if no result or total is null
        }
    } catch (PDOException $e) {
        // Log the error and handle gracefully
        error_log("Database error in getTotalSales: " . $e->getMessage());
        return 0.0; // Return 0.0 on error
    }
}

function getTotalOrders()
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Query to count the total orders
        $stmt = $_db->query("SELECT COUNT(order_id) AS total_orders FROM `order_record`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch a single row as an associative array

        // Check and return the total orders
        if ($result && isset($result['total_orders'])) {
            return (int)$result['total_orders']; // Cast to int for consistent numeric output
        } else {
            return 0; // Return 0 if no result or total_orders is null
        }
    } catch (PDOException $e) {
        // Log the error and handle gracefully
        error_log("Database error in getTotalOrders: " . $e->getMessage());
        return 0; // Return 0 on error
    }
}

function getTotalMembers()
{
    global $_db; // Use the database connection defined in _base.php

    try {
        // Query to count the total members
        $stmt = $_db->query("SELECT COUNT(member_id) AS total_members FROM `member`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch a single row as an associative array

        // Check and return the total members
        if ($result && isset($result['total_members'])) {
            return (int)$result['total_members']; // Cast to int for consistent numeric output
        } else {
            return 0; // Return 0 if no result or total_members is null
        }
    } catch (PDOException $e) {
        // Log the error and handle gracefully
        error_log("Database error in getTotalMembers: " . $e->getMessage());
        return 0; // Return 0 on error
    }
}

//Fetch the GroupBy data 
function getOrdersGroupedByYear($year)
{
    global $_db;
    $stmt = $_db->prepare("
        SELECT MONTH(order_date) AS month, COUNT(*) AS total 
        FROM order_record 
        WHERE YEAR(order_date) = ? 
        GROUP BY MONTH(order_date)
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalesGroupedByYear($year)
{
    global $_db;
    $stmt = $_db->prepare("
        SELECT MONTH(order_date) AS month, SUM(total_amount) AS total 
        FROM order_record 
        WHERE YEAR(order_date) = ? 
        GROUP BY MONTH(order_date)
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsersGroupedByYear($year)
{
    global $_db;
    $stmt = $_db->prepare("
        SELECT MONTH(register_time) AS month, COUNT(*) AS total 
        FROM member 
        WHERE YEAR(register_time) = ? 
        GROUP BY MONTH(register_time)
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLowStockProducts()
{
    global $_db;
    $stmt = $_db->prepare("
        SELECT product_id, description, stock_quantity
        FROM product
        WHERE stock_quantity < 10 AND stock_quantity > 0
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOutOfStockProducts()
{
    global $_db;
    $stmt = $_db->prepare("
        SELECT product_id, description
        FROM product
        WHERE stock_quantity = 0
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function countOutOfStockProducts()
{
    global $_db;
    $stmt = $_db->prepare("SELECT COUNT(*) FROM product WHERE stock_quantity = 0");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function countLowStockProducts()
{
    global $_db; // Use the global database connection
    $stmt = $_db->prepare("SELECT COUNT(*) AS total FROM product  WHERE stock_quantity < 10 AND stock_quantity > 0");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0; // Return the count or 0 if no results
}

/*
function getProductSalesByCategory()
{
    global $_db;
    $stmt = $_db->query("
        SELECT p.category_name, SUM(oi.quantity) AS total_sold
        FROM orderitem oi
        INNER JOIN product p ON oi.product_id = p.product_id
        GROUP BY p.category_name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/

function getProductSalesByCategory($year) {
    global $_db;
    $stmt = $_db->prepare("
        SELECT p.category_name, SUM(oi.quantity) AS total_sold, r.order_date
        FROM orderitem oi
        INNER JOIN product p ON oi.product_id = p.product_id
        INNER JOIN order_record r ON oi.order_id = r.order_id
        WHERE YEAR(r.order_date) = ?
        GROUP BY p.category_name, YEAR(r.order_date)
    ");
    $stmt->execute([$year]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$_user = $_SESSION['user'] ?? null;

function login($user, $url = '/')
{
    $_SESSION['user'] = $user;
    $_SESSION['role'] = $user->role;
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

function save_photos($tmp_name, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($tmp_name) // Passing the correct file path here
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

function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
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
    $value = htmlspecialchars($data); // Prevent XSS by escaping special characters
    // Create the input field with the value and other attributes
    html_input('text', $key, $placeholder, $value, $attr);
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
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    
    echo $defaultOption;
    
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    
    // Close the select element
    echo '</select>';
}


// Generate <input type='checkbox'>
function html_checkbox($key, $status = 'inactive', $attr = '')
{
    $isChecked = ($status === 'active') ? 'checked' : ''; // Check if the status is 'active'

    echo "<label for='$key'>"; // Add a label for accessibility
    echo "<input type='checkbox' id='$key' name='$key' value='active' $isChecked $attr> ";
    echo "</label>";
}



function html_radios($key, $items, $br = false) {

    $value = isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : ($GLOBALS[$key] ?? '');
    
    $output = '<div>';
    foreach ($items as $id => $text) {
        $state = ($id == $value) ? 'checked' : ''; 
        $output .= "<label><input type='radio' id='{$key}_{$id}' name='{$key}' value='{$id}' $state> $text</label>";
        if ($br) {
            $output .= '<br>';
        }
    }
    $output .= '</div>';
    return $output;
}

// Generate search input field
function html_search($key,$placeholder = 'Search by name, email, contact', $data = "", $attr = '') {
    html_input('search', $key, $placeholder, $data, $attr);
}


// ============================================================================
// Email Functions
// ============================================================================

// Demo Accounts:
// --------------
// AACS3173@gmail.com           npsg gzfd pnio aylm
// BAIT2173.email@gmail.com     ytwo bbon lrvw wclr
// liaw.casual@gmail.com        wtpa kjxr dfcb xkhg
// liawcv1@gmail.com            obyj shnv prpa kzvj

// Initialize and return mail object
function get_mail()
{
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'AACS3173@gmail.com';
    $m->Password = 'npsg gzfd pnio aylm';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '😺 Admin');

    return $m;
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

// Is phone?
function is_phone($value) {
    // Ensure it's a string to perform string operations
    $value = strval($value);

    // Check if the phone number starts with "01"
    if (strpos($value, "01") !== 0) {
        return false;
    }

    // Extract the third digit
    $thirdDigit = isset($value[2]) ? $value[2] : null;

    // Check length based on the third digit
    if ($thirdDigit == "1") {
        return strlen($value) == 11; // Length should be 11 if third digit is 1
    } else {
        return strlen($value) == 10; // Length should be 10 otherwise
    }
}

//Is password?
function is_password($value) {
    // Check if the length is at least 8 characters
    if (strlen($value) < 8) {
        return false;
    }

    // Check if there is at least one special character
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
        return false;
    }

    // Check if there is at least one uppercase letter
    if (!preg_match('/[A-Z]/', $value)) {
        return false;
    }

    // All conditions are satisfied
    return true;
}

// Is money?
function is_money($value)
{
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Return base url (host + port)
function base($path = '')
{
    return "http://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}

// Return local root path
function root($path = '')
{
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}



//Product


function fetchProducts($db, $category, $category_id, $name, $sort, $dir)
{
    $params = [];
    $query = "
        SELECT p.*, pp.product_photo_id
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

function html_select_with_subcategories($key, $categories, $default = '- Select One -', $attr = '')
{
    // Get the selected value (category_id or subcategory_id)
    $value = encode($GLOBALS[$key] ?? '');


    echo "<select id='$key' name='$key' $attr>";

    // Default option
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }

    // Iterate through categories and display options
    foreach ($categories as $main_category => $data) {
        $id = $data['id'];  // The category_id
        $has_subcategories = !empty($data['subcategories']);
        
        // Debug: Log the category data
        error_log("Processing category: $main_category (ID: $id)");

        // Main category: Disable if it has subcategories
        $disabled = $has_subcategories ? 'disabled' : '';

        // Set selected for the main category
        $selected = ($id == $value) ? 'selected' : '';
        error_log("Main category selected: $selected");  // Debug: Check if it's selected
        echo "<option value='$id' $selected $disabled>$main_category</option>";

        // Subcategories: If subcategories exist
        if ($has_subcategories) {
            foreach ($data['subcategories'] as $subcategory) {
                $sub_id = $subcategory['id'];  // The subcategory_id
                $sub_name = $subcategory['name'];
                $selected = ($sub_id == $value) ? 'selected' : '';  // Compare with the selected value (subcategory_id)

                // Debug: Log subcategory selection
                error_log("Subcategory: $sub_name (ID: $sub_id), Selected: $selected");

                echo "<option value='$sub_id' $selected>&nbsp;&nbsp;&nbsp;$sub_name</option>";
            }
        }
    }

    echo '</select>';
}



function generate_product_id($category_id, $db)
{
    // Fetch the last product ID in the category
    $stm = $db->prepare('
        SELECT product_id 
        FROM product 
        WHERE product_id LIKE ? 
        ORDER BY product_id DESC 
        LIMIT 1
    ');
    $stm->execute([$category_id . '%']); // Match category_id followed by any number

    $last_id = $stm->fetchColumn();

    // Extract the numeric part and increment it
    // Assuming the format is like XXX0001, XXX0002, etc., we remove the category prefix and parse the number
    $last_number = $last_id ? intval(substr($last_id, strlen($category_id))) : 0; // Skip the category_id part
    $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT); // Increment and pad with leading zeros (4 digits)

    // Return the product ID in the format: category_id + 4-digit number
    return $category_id . $new_number;
}

function generate_photo_id($db)
{
    // Fetch the last product_photo_id
    $stm = $db->prepare('
        SELECT product_photo_id
        FROM product_photo
        ORDER BY product_photo_id DESC
        LIMIT 1
    ');
    $stm->execute();

    $last_id = $stm->fetchColumn();

    // If no IDs are found, start from 1, otherwise increment the last ID
    $new_id = $last_id ? $last_id + 1 : 1;

    return $new_id;
}

function uploadFiles($files, $targetDir = 'product_gallery/', $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'], $maxFileSize = 5 * 1024 * 1024, $width = 200, $height = 200)
{
    $results = []; // Store results for each file

    // Ensure the target directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Loop through the uploaded files
    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileError = $files['error'][$i];
        $fileType = $files['type'][$i];

        // Initialize response for this file
        $fileResult = [
            'fileName' => $fileName,
            'success' => false,
            'message' => '',
            'uploadedPath' => ''
        ];

        // Check for upload errors
        if ($fileError === 0) {
            // Extract file extension
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Validate file extension
            if (in_array($fileExtension, $allowedExtensions)) {
                // Validate file size
                if ($fileSize <= $maxFileSize) {
                    // Use the save_photo() function to upload and resize the image
                    $uniqueFileName = save_photo($files, $targetDir, $width, $height);
                    $uploadPath = $targetDir . $uniqueFileName;

                    $fileResult['success'] = true;
                    $fileResult['uploadedPath'] = $uploadPath;
                    $fileResult['message'] = "File uploaded and resized successfully.";
                } else {
                    $fileResult['message'] = "File size exceeds the limit.";
                }
            } else {
                $fileResult['message'] = "Invalid file type.";
            }
        } else {
            $fileResult['message'] = "Error during file upload.";
        }

        // Add this file's result to the results array
        $results[] = $fileResult;
    }

    return $results;
}




