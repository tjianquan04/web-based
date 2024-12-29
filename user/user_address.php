<?php
require '../_base.php';

$member = $_SESSION['user'];
authMember($member);

$addressArr = getAllAddressbyMemberId($member->member_id);

if (is_post()) {
    if (isset($_POST['edit_address'])) {

        // Handle Edit Address
        $address_id = req('address_id');
        $address_line = req('address_line');
        $state = req('stateInput');
        $postal_code = req('postal_code');
        $is_default = req('is_default') ?? 0;

        $stmt = $_db->prepare('UPDATE address SET address_line = ?, state = ?, postal_code = ?, is_default = ? WHERE address_id = ?');
        $stmt->execute([$address_line, $state, $postal_code, $is_default, $address_id]);
        temp('info', 'Address updated successfully.');

        $addressArr = getAllAddressbyMemberId($member->member_id);
    }

    if (isset($_POST['delete_address'])) {
        // Handle Delete Address
        $address_id = req('address_id');
        $stmt = $_db->prepare('DELETE FROM address WHERE address_id = ?');
        $stmt->execute([$address_id]);
        temp('info', 'Address deleted successfully.');
        $addressArr = getAllAddressbyMemberId($member->member_id);
    }

    if (isset($_POST['add_address'])) {
        $address_id = getNextAddressId();
        $address_line = req('address_line');
        $state = req('add_state');
        $postal_code = req('postal_code');
        $is_default = req('is_default') ?? 0;

        var_dump($_POST['add_address']);
        $stmt = $_db->prepare('INSERT INTO address (address_id, address_line, state, postal_code, is_default, member_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$address_id, $address_line, $state, $postal_code, $is_default, $member->member_id]);
        temp('info', 'Address added successfully.');

        $addressArr = getAllAddressbyMemberId($member->member_id);
    }
}

include '../_head.php';
?>
<link rel="stylesheet" href="../css/user_address.css">

<body>
    <div class="address-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>My Account</h2>
            <a href="user_profile.php">Profile</a>
            <a href="user_address.php">Addresses</a>
            <a href="user_change_password.php">Change Password</a>
            <a href="user_wallet.php">My Wallet</a>
        </div>

        <!-- Address Details Section -->
        <div class="address-details">
            <div class="address-header">
                <h2>Member Addresses</h2>
                <button class="add-btn">Add New Address</button>
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
                                    <h4><?= $member->name ?></h4>
                                    <p><?= $member->contact ?></p>
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
        <input type="text" name="address_line" id="addressLineInput" value="<?= $address_line ?>" placeholder="Enter address line">

        <label for="stateInput"><strong>State</strong></label>
        <?php html_select('stateInput', $_states, '', 'class="input-field"', '' . $address->state) ?>

        <label for="postalCodeInput"><strong>Postal Code</strong></label>
        <input type="text" name="postal_code" id="postalCodeInput" value="<?= $postal_code ?>" placeholder="Enter postal code">

        <label><strong> Set as Default</strong> </label>
        <input type="checkbox" name="is_default" id="defaultCheckbox" value="1">

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

        <label for="addStateSelect"><strong>State:</strong></label>
        <?php html_select('addStateSelect', $_states, '', 'class="input-field"') ?>

        <label for="addPostalCodeInput"><strong>Postal Code</strong></label>
        <input type="text" name="postal_code" id="addPostalCodeInput" placeholder="Enter postal code">

        <label><strong> Set as Default </strong></label>
        <input type="checkbox" name="is_default" id="addDefaultCheckbox" value="1">

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

            let existingDefault = document.querySelector('.default-address') !== null;

            // Function to validate address input
            function validateAddress(addressLine, state, postalCode, isDefault, existingDefault) {
                if (!addressLine || addressLine.trim() === '') {
                    alert('Address is required.');
                    return false;
                }
                if (!state || state.trim() === '') {
                    alert('State is required.');
                    return false;
                }
                if (!postalCode || !/^\d{5}$/.test(postalCode)) {
                    alert('Postal code must be exactly 5 digits.');
                    return false;
                }
                if (isDefault && existingDefault) {
                    alert('Only one default address is allowed.');
                    return false;
                }
                return true;
            }

            // Edit Button Logic
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const addressId = button.dataset.addressId;
                    const addressLine = button.dataset.addressLine;
                    const state = button.dataset.state;
                    const postalCode = button.dataset.postalCode;
                    const isDefault = button.dataset.isDefault === '1';

                    document.getElementById('addressIdInput').value = addressId;
                    document.getElementById('addressLineInput').value = addressLine;
                    document.getElementById('stateInput').value = state;
                    document.getElementById('postalCodeInput').value = postalCode;
                    document.getElementById('defaultCheckbox').checked = isDefault;

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

            //edit address
            document.getElementById('popupForm').addEventListener('submit', (e) => {
                const addressLine = document.getElementById('addressLineInput').value;
                const state = document.getElementById('stateInput').value;
                const postalCode = document.getElementById('postalCodeInput').value;
                const isDefault = document.getElementById('defaultCheckbox').checked;

                let existingDefault = document.querySelector('.default-address') !== null;

                if (!validateAddress(addressLine, state, postalCode, isDefault, existingDefault)) {
                    e.preventDefault(); // Prevent form submission
                }
            });

            //add address
            document.getElementById('addPopupForm').addEventListener('submit', (e) => {
                const addressLine = document.getElementById('addAddressLineInput').value;
                const state = document.getElementById('addStateSelect').value;
                const postalCode = document.getElementById('addPostalCodeInput').value;
                const isDefault = document.getElementById('addDefaultCheckbox').checked;

                let existingDefault = document.querySelector('.default-address') !== null;

                if (!validateAddress(addressLine, state, postalCode, isDefault, existingDefault)) {
                    e.preventDefault(); // Prevent form submission
                }
            });

            // Delete Address Confirmation
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const addressId = button.dataset.addressId;
                    const confirmation = confirm('Are you sure you want to delete this address?');

                    if (confirmation) {
                        const deleteForm = document.getElementById('deleteForm');
                        document.getElementById('deleteAddressId').value = addressId;
                        deleteForm.submit(); // Submit the delete form
                    }
                });
            });
        });
    </script>
</body>

<?php include '../_foot.php'; ?>