<?php
require '../_base.php';

if (is_post()) {
    $_db->query('DELETE FROM register_token WHERE expire < NOW()');

    $tokenId = req('token_id');
    $submit_otp     = req('otp');

    //Get OTP and Member id 
    $stmt = $_db->prepare('SELECT otp_number, member_id FROM register_token WHERE token_id = ?');
    $stmt->execute([$tokenId]);
    $getData = $stmt->fetch();

    $userId = $getData['member_id'];
    $validOTP = $getData['otp_number'];

    if(empty($submit_otp)){
        $_err['otp'] = 'OTP code is required.';
    }else if ($submit_otp != $validOTP){
        $_err['otp'] = 'Invalid OTP code. Please try again !';
    }

    if (empty($_err)) {

        // Update User Account Status
        $stm = $_db->prepare('UPDATE member SET status = ? WHERE member_id = ?');
        $stm->execute([1, $userId]);

        // Delete Token
        $stm = $_db->prepare('DELETE FROM register_token WHERE token_id = ?');
        $stm->execute([$tokenId]);

        temp('info', 'Account successfully activated.');
        redirect('login.php');
        exit;
    } 
}

if (isset($_GET['resend']) && isset($_GET['token_id'])) {
    $oldTokenId = $_GET['token_id'];

    // Regenerate Token and OTP
    $stmt = $_db->prepare('SELECT member_id, email FROM register_token JOIN member USING(member_id) WHERE token_id = ?');
    $stmt->execute([$oldTokenId]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = $user['member_id'];
        $email = $user['email'];
        $tokenId = SHA1(uniqid() . rand());
        $otp_num = rand(100000, 999999);

        // Update Token
        $stm = $_db->prepare('
            INSERT INTO register_token (token_id, otp_number, expire, member_id)
            VALUES (?, ?, ADDTIME(NOW(),"00:05"), ?)
        ');
        $stm->execute([$tokenId, $otp_num, $userId]);

        // Send Email
        $m = get_mail();
        $m->addAddress($email, 'User');
        $m->isHTML(true);
        $m->Subject = 'Resend OTP Code';
        $m->Body = "
            <p>Dear User,</p>
           <p>
               Your OTP number is $otp_num. Please activate your account using this OTP number.
            </p>
            <p>From, Admin</p>
        ";
        $m->send();

        // Refresh the page with the new token_id
        header("Location: ?token_id=$newTokenId");
        exit;
    }
}
?>

<body>
    <main>
        <div class="form-section">
            <div class="form-header">
                <h1>Activate Your Account</h1>
            </div>
            <form method="post" class="form">
                <input type="hidden" name="token_id" value="<?php echo htmlspecialchars($_GET['token_id'] ?? ''); ?>" />
                <label for="otp">Enter OTP:</label>
                <input type="number" name="otp" placeholder="Enter your OTP" required />
                <?php err('otp_number'); ?>
                <button type="submit">Submit</button>
                <button type="button" id="resendOtp">Resend OTP</button>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('resendOtp').addEventListener('click', function () {
            const tokenId = new URLSearchParams(window.location.search).get('token_id');
            if (tokenId) {
                window.location.href = `?resend=1&token_id=${tokenId}`;
            }
        });
    </script>
</body>
