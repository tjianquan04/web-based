<?php
require '_base.php';
include '_head.php';
$_title = 'Shopping-Guide | Boost.do';
?>

<style>
    .shoppingGuide-container {
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 10px 10px 6px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        font-family: "Roboto", serif;
        font-weight: 300;
        font-style: normal;
    }

    .collapsible {
        background-color: #e5e5e5;
        color: rgb(0, 0, 0);
        cursor: pointer;
        padding: 15px;
        border: none;
        text-align: left;
        outline: none;
        font-size: 16px;
        width: 100%;
        border-radius: 4px;
        margin-bottom: 10px;
        box-shadow: 10px 10px 6px rgba(0, 0, 0, 0.1);
    }

    .collapsible:hover {
        background-color: #bdbec1;
    }

    .collapsible-content {
        padding: 15px;
        display: none;
        overflow: hidden;
        background-color: #fffdfd;

    }

    .collapsible-content p {
        margin: 0;
    }
</style>
</head>

<body>
    
    <div class="shoppingGuide-container">
    <a href="javascript:history.back()" class="back-button">
    <i class="fa-solid fa-arrow-left-long"></i></a>
    <br>
        <h1>Shopping Guide</h1>
<br>
        <button class="collapsible">Payment Methods</button>
        <div class="collapsible-content">
            <p>We provide the following payment methods:</p>
            <ul>
                <li>Online Banking</li>
                <li>Credit Card</li>
                <li>eWallet</li>
            </ul>
        </div>

        <button class="collapsible">Product Care Tips</button>
        <div class="collapsible-content">
            <p>To ensure the longevity of your badminton equipment:</p>
            <ul>
                <li><b> Racquets</b>: Store in a racquet bag to avoid scratches and tension loss. Avoid extreme temperatures.</li>
                <li><b>Shuttlecocks</b>: Keep them in a cool, dry place to maintain flight quality.</li>
            </ul>
        </div>

        <button class="collapsible">Shipping Methods</button>
        <div class="collapsible-content">
            <p>Your order will be processed immediately after the payment is confirmed, and you will receive the ordered goods within seven working days (excluding Saturdays, Sundays, and national holidays).</p>
            <p>We ship out the goods via:</p>
            <ul>
                <li>Post Office</li>
                <li>Easy Parcel</li>
            </ul>
            <p>If your order was unsuccessful, you will be notified within two working days after receiving your order.</p>
        </div>


        <button class="collapsible">Membership Policy</button>
        <div class="collapsible-content">
            <p>Only registered members can purchase from our online store.</p>

            <p>Join Us now! <a href="/user/login.php">Login/Register</a></p>
        </div>
    </div>


    <?php
    include '_foot.php';
    ?>
    <script>
        const collapsibles = document.querySelectorAll(".collapsible");

        collapsibles.forEach(button => {
            button.addEventListener("click", () => {
                button.classList.toggle("active");
                const content = button.nextElementSibling;

                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        });
    </script>