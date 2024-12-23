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
        $state = req('state');
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
            redirect('/');
        }
    }

    if (isset($_POST['delete_address'])) {
        // Handle Delete Address
        $address_id = req('address_id');
        $stmt = $_db->prepare('DELETE FROM address WHERE address_id = ?');
        $stmt->execute([$address_id]);
        temp('info', 'Address deleted successfully.');
        redirect('/');
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
            <a href="user_top_up.php?id=<?= $s->member_id ?>">Top Up</a>
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

        <label for="addressLineInput">Address Line</label>
        <?php html_text('address_line', 'Enter address line'); ?>
        <?= err('address_line') ?>

        <label for="stateInput">State</label>
        <?php global $_states;
        html_select('state', $_states, '- Select State -'); ?>
        <?= err('state') ?>

        <label for="postalCodeInput">Postal Code</label>
        <?php html_text('postal_code', 'Enter postal code'); ?>
        <?= err('postal_code') ?>

        <label for="defaultCheckbox">Set as Default</label>
        <?php html_checkbox('is_default', 'inactive'); ?>
        <?= err('is_default') ?>

        <button type="submit" name="edit_address" class="submit-btn">Save</button>
        <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
    </form>

    <form method="post" id="deleteForm">
        <input type="hidden" name="address_id" id="deleteAddressId">
        <input type="hidden" name="delete_address" value="1">
    </form>

    <script>
        const overlay = document.getElementById('overlay');
        const popupForm = document.getElementById('popupForm');
        const cancelBtn = document.getElementById('cancelBtn');

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('addressIdInput').value = button.dataset.addressId;
                document.getElementById('addressLineInput').value = button.dataset.addressLine;
                document.getElementById('stateInput').value = button.dataset.state;
                document.getElementById('postalCodeInput').value = button.dataset.postalCode;
                document.getElementById('defaultCheckbox').checked = button.dataset.isDefault == '1';

                overlay.style.display = 'block';
                popupForm.style.display = 'block';
            });
        });

        cancelBtn.addEventListener('click', () => {
            overlay.style.display = 'none';
            popupForm.style.display = 'none';
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete this address?')) {
                    document.getElementById('deleteAddressId').value = button.dataset.addressId;
                    document.getElementById('deleteForm').submit();
                }
            });
        });

        overlay.addEventListener('click', () => {
            overlay.style.display = 'none';
            popupForm.style.display = 'none';
        });
    </script>
</body>

<?php include '../_foot.php'; ?>