<?php
require '../_base.php';

$cooldownTime = 5 * 60; //5 minutes

if (is_post()) {
    $_db->query('DELETE FROM register_token WHERE expire < NOW()');

    $tokenId = req('token_id');
    $submit_otp     = req('otp');
    
    //Get OTP and Member id 
    $stmt = $_db->prepare('SELECT otp_number, member_id FROM register_token WHERE token_id = ?');
    $stmt->execute([$tokenId]);
    $getData = $stmt->fetch();

    $userId = $getData->member_id;
    $validOTP = $getData->otp_number;


    if(empty($submit_otp)){
        $_err['otp_number'] = 'OTP code is required.';
    }else if ($submit_otp !== $validOTP){
        $_err['otp_number'] = 'Invalid OTP code. Please try again !';
    }

    if (empty($_err)) {

        // Update User Account Status
        $stm = $_db->prepare('UPDATE member SET status = ? WHERE member_id = ?');
        $stm->execute(['Active', $userId]);

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
    $stmt = $_db->prepare('SELECT member_id, email,last_email_sent FROM register_token JOIN member USING(member_id) WHERE token_id = ?');
    $stmt->execute([$oldTokenId]);
    $user = $stmt->fetch();

    $currentTime = time();
    $lastEmailSentTime = $user->last_email_sent ? strtotime($user->last_email_sent) : 0;

    if ($currentTime - $lastEmailSentTime < $cooldownTime) {
                // Cooldown still active
        $remainingTime = $cooldownTime - ($currentTime - $lastEmailSentTime);
        $_err['cooldown_error'] = "You can request for OTP numbers again in " . gmdate("i:s", $remainingTime) . ".";
     }

    if (empty($_err)) {
        $userId = $user->member_id;
        $email = $user->email;
        $tokenId = SHA1(uniqid() . rand());
        $otp_num = rand(100000, 999999);

        // Update Token
        $stm = $_db->prepare('
            INSERT INTO register_token (token_id, otp_number, expire, member_id)
            VALUES (?, ?, ADDTIME(NOW(),"00:05"), ?)
        ');
        $stm->execute([$tokenId, $otp_num, $userId]);

        $stmt = $_db->prepare('
        UPDATE FROM member SET last_email_sent = ? WHERE member_id = ?');
        $stmt->execute([$currentTime, $userId]);

        // Send Email
        $m = get_mail();
        $m->addAddress($email, 'User');
        $m->isHTML(true);
        $m->Subject = 'Resend OTP Code';
        $m->Body = "
           <p>Dear $user->name,</p>
            <h1 style='color: green'>Activate Boots.Do Account</h1>
            <p>
               Your OTP code is </p><strong>$otp_num</strong><br> 
               <p>Please activate account using the OTP number. </p>             
               <p>This OTP code will expire in 5 minutes.</p>         
            <p>From, Boots.Do Admin</p>
        ";
        $m->send();

        // Refresh the page with the new token_id
        header("Location: ?token_id=$newTokenId");
        exit;
    }
}
?>
 <link rel="stylesheet" href="/css/verify_otp.css">
<body>
    <div class="send-otp-container">
        <div class="form-container">
            <button class="back-button" onclick="history.back()">&larr;</button>
            <h1>Activate Your Account</h1>
            <p>Please check your mailbox to get OTP code</p>

            <form method="post" class="form">
                <input type="hidden" name="token_id" value="<?php echo htmlspecialchars($_GET['token_id'] ?? ''); ?>" />
                
                <div class="form-group">
                <label for="otp"><i class="fa fa-lock"></i> OTP Code : </label>
                <input type="text" name="otp" placeholder="Enter your OTP" required />
                <?php err('otp_number'); ?>
                <?= err('cooldown_error') ?>
                </div>

                <button type="submit" class="form-btn">Submit</button><br>
                <button type="button" id="resendOtp" class="form-btn">Resend OTP</button>
            </form>
        </div>
        <div class="image-container">
            <img src="/image/forgot-password.png" alt="Reset Password Image">
        </div>
    </div>

    <script>
        document.getElementById('resendOtp').addEventListener('click', function () {
            const tokenId = new URLSearchParams(window.location.search).get('token_id');
            if (tokenId) {
                window.location.href = `?resend=1&token_id=${tokenId}`;
            }
        });
    </script>
</body>
