<?php
require '../_base.php';
$_title = 'Register | Boost.do';
// ----------------------------------------------------------------------------

if (is_post()) {
    $name     = req('name');
    $email    = req('email');
    $password = req('password');
    $confirmPassword = req('confirm_password');

    // Validate Email
    if (empty($email)) {
        $_err['email'] = 'Email is required.';
    } else if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $_err['email'] = 'Invalid email format.';
    } else if (is_exists($email, 'member', 'email')) {
        $_err['email'] = 'Email has already exist.';
    }

    // Validate Password
    if (empty($password)) {
        $_err['password'] = 'Password is required.';
    } else if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $_err['password'] = 'Invalid password format. (eg. Aa1*XXXX)';
    }

    // Validate Confirm Password
    if (empty($confirmPassword)) {
        $_err['confirm_password'] = 'Confirm password is required.';
    } else if ($password != $confirmPassword) {
        $_err['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($_err)) {
        $currentDateTime = date('Y-m-d H:i:s');
        $user_id = getNextUserId();

        // Insert the user into the database
        $stmt = $_db->prepare("INSERT INTO member (member_id, name, email, contact, password, register_date, status, profile_photo, last_email_sent) VALUES (?, ?, ?, ? ,?, ?, ?, ?,?)");
        $stmt->execute([$user_id, $name, $email, '-', SHA1($password), $currentDateTime, 'Inactive', 'unknown.jpg',$currentDateTime]);

        // Generate and insert token
        $token_id = SHA1(uniqid() . rand());
        $otp_num = ''.rand(100000, 999999);
        $stm = $_db->prepare('
            INSERT INTO register_token (token_id,otp_number, expire, member_id)
            VALUES (?, ?, ADDTIME(NOW(),"00:05"), ?);
        ');
        $stm->execute([$token_id, $otp_num, $user_id]);

        // Generate token URL and send email
        $m = get_mail();
        $m->addAddress($email, $name);
        $m->isHTML(true);
        $m->Subject = 'Verify Boots Account';

        $m->Body = "
            <p>Dear $name,</p>
            <h1 style='color: green'>Activate Boots.Do Account</h1>
            <p>
               Your OTP number is </p><strong>$otp_num</strong><br> 
               <p>Please activate account using the OTP number.</p>
               <p>This OTP code will expire in 5 minutes.</p>           
            <p>From, Boots.Do Admin</p>
        ";
        $m->send();

        redirect('verify_otp.php?token_id=' . $token_id);
    }
}

// ----------------------------------------------------------------------------
$_title = 'Sign Up | Boost.do';
include '../_head.php';
?>

<link rel="stylesheet" href="../css/register.css">

<body>
    <main>
    <body>
    <div class="login-container">
        <div class="form-container">
                <h1>Create an Account</h1>
            <form method="post" class="form">
                <div class="form-group">
                <label for="name">Name :</label>
                <?php html_text('name', 'e.g. henry', '', 'class="form-control"'); ?>
                <?php err('name'); ?>
                </div>

                <div class="form-group">
                <label for="email">Email Address :</label>
                <?php html_email('email', 'e.g. henry@gmail.com', '', 'class="form-control"'); ?>
                <?php err('email'); ?>
                </div>

                <div class="form-group">
                <label for="password">Password :</label>
                <?php html_password('password', 'e.g. Henry@123', '', 'class="form-control" '); ?>
                <?php err('password'); ?>
                </div>

                <div class="form-group">
                <label for="confirm_password">Confirm Password : </label>
                <?php html_password('confirm_password', 'Re-enter your password', '', 'class="form-control" '); ?>
                <?php err('confirm_password'); ?>
                </div>
                
                <button type="submit" class="btn-signup">Sign Up</button>
            </form>
            <p class="signIn-text">Already have an account? <a href="login.php">Log in here</a></p>
        </div>

        <div class="slideshow-container">
            <div class="mySlides fade">
                <img src="../image/badminton_shop.png" alt="Image 1">
            </div>

            <div class="mySlides fade">
                <img src="../image/badminton_shop1.png" alt="Image 2">
            </div>

            <div class="mySlides fade">
                <img src="../image/badminton_shop2.png" alt="Image 3">
            </div>
        </div>
    </div>
    </main>
    <script src="/js/main.js"></script>
</body>

<?php include '../_foot.php'; ?>
