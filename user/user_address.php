<?php
require '../_base.php';

$memberId = req('id');

$s = getMemberbyId($memberId);
$addressArr = getAllAddressbyMemberId($memberId);

if (is_post()) {
    if (isset($_POST['edit_address'])) {

        // Handle Edit Address
        $address_id = req('address_id');
        $address_line = req('address_line');
        $state = req('stateInput');
        $postal_code = req('postal_code');
        $is_default = req('is_default') ?? 0;

        // Validation
        $_err = [];
        if (empty($address_line)) {
            $_err['address_line'] = 'Address is required.';
        }
        if (empty($state)) {
            $_err['state'] = 'State is required.';
        }
        if (empty($postal_code) || !preg_match('/^\d{5}$/', $postal_code)) {
            $_err['postal_code'] = 'Postal code must be exactly 5 digits.';
        }

        if (!$is_default && !$_err) {
            // Check if another default exists
            $stmt = $_db->prepare('SELECT COUNT(*) FROM address WHERE member_id = ? AND is_default = 1 AND address_id != ?');
            $stmt->execute([$memberId, $address_id]);
            $existingDefault = $stmt->fetchColumn();

            if ($is_default && $existingDefault) {
                $_err['is_default'] = 'Only one default address is allowed.';
            }
        }

        if (!$_err) {
            $stmt = $_db->prepare('UPDATE address SET address_line = ?, state = ?, postal_code = ?, is_default = ? WHERE address_id = ?');
            $stmt->execute([$address_line, $state, $postal_code, $is_default, $address_id]);
            temp('info', 'Address updated successfully.');
            redirect('/user/user_address.php?id=' . $memberId);
        }else{
            temp('info', 'Address updated failed.');
            redirect('/user/user_address.php?id=' . $memberId);
        }
    }

    if (isset($_POST['delete_address'])) {
        // Handle Delete Address
        $address_id = req('address_id');
        $stmt = $_db->prepare('DELETE FROM address WHERE address_id = ?');
        $stmt->execute([$address_id]);
        temp('info', 'Address deleted successfully.');
        redirect('/user/user_address.php?id=' . $memberId);
    }

    if (isset($_POST['add_address'])) {
        $address_id = getNextAddressId();
        $address_line = req('address_line');
        $state = req('add_state');
        $postal_code = req('postal_code');
        $is_default = req('is_default') ?? 0;

        // Validation
        $_err = [];
        if (empty($address_line)) {
            $_err['address_line'] = 'Address is required.';
        }
        if (empty($state)) {
            $_err['state'] = 'State is required.';
        }
        if (empty($postal_code) || !preg_match('/^\d{5}$/', $postal_code)) {
            $_err['postal_code'] = 'Postal code must be exactly 5 digits.';
        }

        if (!$is_default && !$_err) {
            // Check if another default exists
            $stmt = $_db->prepare('SELECT COUNT(*) FROM address WHERE member_id = ? AND is_default = 1');
            $stmt->execute([$memberId]);
            $existingDefault = $stmt->fetchColumn();

            if ($is_default && $existingDefault) {
                $_err['is_default'] = 'Only one default address is allowed.';
            }
        }

        if (!$_err) {
            $stmt = $_db->prepare('INSERT INTO address (address_id, address_line, state, postal_code, is_default, member_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$address_id, $address_line, $state, $postal_code, $is_default, $memberId]);
            temp('info', 'Address added successfully.');
            redirect('/user/user_address.php?id=' . $memberId);
        }
    }
}

include '../_head.php';
?>
<link rel="stylesheet" href="../css/user_address.css">

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>My Account</h2>
            <a href="user_profile.php?id=<?= $s->member_id ?>">Profile</a>
            <a href="user_address.php?id=<?= $s->member_id ?>" class="active-link">Addresses</a>
            <a href="user_change_password.php?id=<?= $s->member_id ?>">Change Password</a>
            <a href="user_wallet.php?id=<?= $s->member_id?>">My Wallet</a>
        </div>

        <!-- Address Details Section -->
        <div class="address-details">
            <div class="address-header">
                <h2>Member Addresses</h2>
                <button data-get="add_address.php?id=<?= $s->member_id ?>" class="add-btn">Add New Address</button>
            </div>

            <div class="address-content">
                <?php if (empty($addressArr)): ?>
                    <div class="no-address">
                        No addresses found. Please add a new address.
                    </div>
                <?php else: ?>
                    <?php foreach ($addressArr as $address): ?>
                        <div class="address-card <?= $address->is_default ? 'default-address' : '' ?>">
                            <div class="card-header">
                                <div class="header-info">
                                    <h4><?= $s->name ?></h4>
                                    <p><?= $s->contact ?></p>
                                </div>
                                <div class="header-actions">
                                    <button class="edit-btn" data-address-id="<?= $address->address_id ?>" data-address-line="<?= $address->address_line ?>" data-state="<?= $address->state ?>" data-postal-code="<?= $address->postal_code ?>" data-is-default="<?= $address->is_default ?>">Edit</button>
                                    <button class="delete-btn" data-address-id="<?= $address->address_id ?>">Delete</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><?= $address->address_line . ', ' . $address->postal_code . ', ' . $address->state ?></p>
                            </div>
                            <div class="card-footer">
                                <?php if ($address->is_default): ?>
                                    <span class="default-badge">(Default Address)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- Overlay and Form -->
    <div class="overlay" id="overlay"></div>
    <form method="post" class="popup" id="popupForm">
        <h2>Edit Address</h2>
        <input type="hidden" name="address_id" id="addressIdInput">
        <label for="addressLineInput"><strong>Address Line</strong></label>
        <input type="text" name="address_line" id="addressLineInput" value="<?= $address_line?>" placeholder="Enter address line">
        <?= err('addressLineInput') ?>
        <label for="stateInput"><strong>State</strong></label>
        <?php html_select('stateInput', $_states, '', 'class="input-field"', ''.$address->state) ?>
        <?= err('stateInput') ?>
        <label for="postalCodeInput"><strong>Postal Code</strong></label>
        <input type="text" name="postal_code" id="postalCodeInput" value="<?=$postal_code ?>" placeholder="Enter postal code">
        <?= err('postalCodeInput') ?>
        <label><strong> Set as Default</strong> </label>
        <input type="checkbox" name="is_default" id="defaultCheckbox" value="1">
        <?= err('defaultCheckbox') ?>

        <button type="submit" name="edit_address" class="submit-btn">Save</button>
        <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
    </form>

    <form method="post" id="deleteForm">
        <input type="hidden" name="address_id" id="deleteAddressId">
        <input type="hidden" name="delete_address" value="1">
    </form>

    <!-- Overlay and Add Address Form -->
    <div class="overlay" id="addOverlay"></div>
    <form method="post" class="popup" id="addPopupForm">

        <h2>Add New Address</h2>
        <label for="addAddressLineInput"><strong>Address Line</strong></label>
        <input type="text" name="address_line" id="addAddressLineInput" placeholder="Enter address line">
        <?= err('addAddressLineInput') ?>
        <label for="addStateSelect"><strong>State:</strong></label>
        <?php html_select('add_state', $_states, '', 'class="input-field"') ?>
        <?= err('add_state') ?>
        <label for="addPostalCodeInput"><strong>Postal Code</strong></label>
        <input type="text" name="postal_code" id="addPostalCodeInput" placeholder="Enter postal code">
        <?= err('addPostalCodeInput') ?>
        <label><strong> Set as Default </strong></label>
        <input type="checkbox" name="is_default" id="addDefaultCheckbox" value="1">
        <?= err('addDefaultCheckbox') ?>
        <button type="submit" name="add_address" class="submit-btn">Add</button>
        <button type="button" class="cancel-btn" id="addCancelBtn">Cancel</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('overlay');
            const popupForm = document.getElementById('popupForm');
            const cancelBtn = document.getElementById('cancelBtn');

            const addOverlay = document.getElementById('addOverlay');
            const addPopupForm = document.getElementById('addPopupForm');
            const addCancelBtn = document.getElementById('addCancelBtn');
            const addAddressButton = document.querySelector('.add-btn');

            // Edit Button Logic
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', () => {
                    // Get data from button attributes
                    const addressId = button.dataset.addressId;
                    const addressLine = button.dataset.addressLine;
                    const state = button.dataset.state;
                    const postalCode = button.dataset.postalCode;
                    const isDefault = button.dataset.isDefault === '1';

                    // Populate form fields
                    document.getElementById('addressIdInput').value = addressId;
                    document.getElementById('addressLineInput').value = addressLine;
                    document.getElementById('stateInput').value = state;
                    document.getElementById('postalCodeInput').value = postalCode;
                    document.getElementById('defaultCheckbox').checked = isDefault;

                    // Show popup
                    overlay.style.display = 'block';
                    popupForm.style.display = 'block';
                });
            });

            // Add Address Button Logic
            addAddressButton.addEventListener('click', () => {
                addOverlay.style.display = 'block';
                addPopupForm.style.display = 'block';
            });

            // Cancel Buttons
            cancelBtn.addEventListener('click', () => {
                overlay.style.display = 'none';
                popupForm.style.display = 'none';
            });

            addCancelBtn.addEventListener('click', () => {
                addOverlay.style.display = 'none';
                addPopupForm.style.display = 'none';
            });

            // Close Popup on Overlay Click
            overlay.addEventListener('click', () => {
                overlay.style.display = 'none';
                popupForm.style.display = 'none';
            });

            addOverlay.addEventListener('click', () => {
                addOverlay.style.display = 'none';
                addPopupForm.style.display = 'none';
            });

        });


        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete this address?')) {
                    document.getElementById('deleteAddressId').value = button.dataset.addressId;
                    document.getElementById('deleteForm').submit();
                }
            });
        });
    </script>
</body>

<?php include '../_foot.php'; ?>