<?php
require '../_base.php';

// Get the product ID from the query string
$product_id = req('product_id');

// Fetch all photos for the product
$photo_stm = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ?');
$photo_stm->execute([$product_id]);
$photos = $photo_stm->fetchAll();

// Check the number of existing photos
$photo_count = count($photos);

// Handle photo actions (delete or set default)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_photo'])) {
        $photo_id = req('photo_id');

        // Check if the deleted photo is the default
        $photo_stm = $_db->prepare('SELECT default_photo FROM product_photo WHERE product_photo_id = ?');
        $photo_stm->execute([$photo_id]);
        $deleted_photo = $photo_stm->fetch();

        // Delete the selected photo
        $_db->prepare('DELETE FROM product_photo WHERE product_photo_id = ?')->execute([$photo_id]);

        // If the deleted photo was the default, set a new default
        if ($deleted_photo && $deleted_photo->default_photo == 1) {
            // Get all remaining photos for the product
            $remaining_photos_stm = $_db->prepare('SELECT product_photo_id FROM product_photo WHERE product_id = ?');
            $remaining_photos_stm->execute([$product_id]);
            $remaining_photos = $remaining_photos_stm->fetchAll();

            // If there are still photos left, randomly select a new default photo
            if ($remaining_photos) {
                $random_photo = $remaining_photos[array_rand($remaining_photos)];
                $_db->prepare('UPDATE product_photo SET default_photo = 1 WHERE product_photo_id = ?')->execute([$random_photo->product_photo_id]);
            }
        }
    } elseif (isset($_POST['set_default'])) {
        $photo_id = req('photo_id');

        // Remove default from all other photos first
        $_db->prepare('UPDATE product_photo SET default_photo = 0 WHERE product_id = ?')->execute([$product_id]);

        // Set the selected photo as the default
        $_db->prepare('UPDATE product_photo SET default_photo = 1 WHERE product_photo_id = ?')->execute([$photo_id]);
    }

    if (isset($_POST['add_photo']) && isset($_FILES['product_photos'])) {
        $product_photos = $_FILES['product_photos'];
        $errors = [];  // Array to store errors
        
        foreach ($product_photos['name'] as $key => $photo_name) {
            // Check if the file is an image
            if (!str_starts_with($product_photos['type'][$key], 'image/')) {
                $errors[] = 'Each photo must be an image.';
                break;
            }
            // Check the file size (1MB max)
            if ($product_photos['size'][$key] > 1 * 1024 * 1024) {
                $errors[] = 'Each photo must not exceed 1MB.';
                break;
            }
        }
    
        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }
    
        // Save photos and get paths
        $photo_paths = [];
        foreach ($product_photos['tmp_name'] as $key => $tmp_name) {
            $photo_filename = save_photos($tmp_name, '../product_gallery');
            $photo_paths[] = $photo_filename;
        }
    
        // Insert photos into product_photo table
        $stmt = $_db->prepare("INSERT INTO product_photo (product_photo_id, default_photo, product_id) VALUES (?, ?, ?)");
    
        foreach ($photo_paths as $index => $photo_filename) {
            $stmt->execute([$photo_filename, 0, $product_id]);
        }
    
    }

    redirect();
}

// Function to get the current default photo ID for the product
function getCurrentDefaultPhotoId($product_id)
{
    global $_db;
    $stmt = $_db->prepare('SELECT product_photo_id FROM product_photo WHERE product_id = ? AND default_photo = 1');
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    return $result ? $result->product_photo_id : null;
}
?>

<style>
    /* Reset some default styling */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        overflow: auto;
    }

    /* Navigation Header Styling */
    header {
        position: sticky;
        top: 0;
        left: 0;
        width: 100%;
        display: flex;
        align-items: center;
        padding: 10px 20px;
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        z-index: 10;
    }

    header .back-button {
        text-decoration: none;
        font-size: 1.5em;
        color: #e94e77;
        margin-right: 10px;
        transition: color 0.3s ease;
    }

    header .back-button:hover {
        color: #d43f63;
    }

    header h1 {
        font-size: 1.2em;
        color: #555;
        font-weight: normal;
    }

    /* Product Photos Section */
    .product-photos {
        text-align: center;
        margin-top: 20px;
        width: 100%;
    }

    .product-photos h3 {
        font-size: 1.5em;
        margin-bottom: 20px;
        color: #333;
    }

    .photo-gallery {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        width: 100%;
    }

    .product-photo {
        width: 80%;
        max-width: 600px;
        height: auto;
        object-fit: contain;
    }

    p {
        font-size: 1.2em;
        color: #777;
        margin-top: 20px;
    }

    .photo-container {
        position: relative;
        display: inline-block;
        width: 80%;
        max-width: 600px;
        height: auto;
    }

    .cover-photo-message {
        display: none;
        position: absolute;
        top: 10px;
        left: 10px;
        background-color: rgba(234, 234, 234, 0.7);
        color: black;
        padding: 10px;
        border-radius: 5px;
        font-size: 16px;
        text-align: center;
    }

    .photo-container:hover .cover-photo-message {
        display: block;
    }

    /* Popup for photo actions */
    .popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        justify-content: center;
        align-items: center;
    }

    .popup-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        max-width: 400px;
        text-align: center;
    }

    .popup .close-btn {
        margin-top: 20px;
        background-color: #e94e77;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .popup .close-btn:hover {
        background-color: #d43f63;
    }

    .delete-btn:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }


    /* Style for the upload form */
    .add-photo {
        text-align: center;
        margin: 20px 0;
    }

    .add-photo input[type="file"] {
        margin-right: 10px;
    }

    .add-photo button {
        background-color:rgb(244, 131, 193);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .add-photo button:hover {
        background-color:rgb(255, 213, 223);
    }
</style>

<header>
    <a href="product_details.php" class="back-button">&larr;</a>
    <h1>Product Image</h1>
</header>

<div class="add-photo">
    <form id="add_new_photos_form" method="POST" enctype="multipart/form-data">
        <label for="product_photos">Upload a New Photo</label>
        <input type="file" name="product_photos[]" id="add_photos_id" multiple>
        <button type="submit" name="add_photo" id="upload_button">Add Photo</button>
    </form>
    <!-- Message container -->
    <p id="photo-message" style="color: red; display: none;"></p>
</div>

<!-- Photo Gallery Section -->
<div class="product-photos">
    <?php if ($photos): ?>
        <div class="photo-gallery">
            <?php foreach ($photos as $photo): ?>
                <div class="photo-container">
                    <img
                        src="../product_gallery/<?= $photo->product_photo_id ?>"
                        alt="Product Photo"
                        class="product-photo"
                        onclick="showPopup('<?= $photo->product_photo_id ?>', <?= $photo->default_photo ?>)" />
                    <?php if ($photo->default_photo): ?>
                        <div class="cover-photo-message">This is the current cover photo.</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No photos available for this product.</p>
    <?php endif; ?>
</div>

<!-- Popup for deleting or setting default photo -->
<div id="popup" class="popup">
    <div class="popup-content">
        <h2>What would you like to do?</h2>

        <!-- Form for deleting a photo -->
        <form id="delete-photo-form" method="POST">
            <input type="hidden" name="photo_id" id="delete-photo-id">
            <div class="actions">
                <button type="submit" name="delete_photo" class="delete-btn" <?= count($photos) == 1 ? 'disabled' : '' ?>>Delete Photo</button>
            </div>
        </form>

        <!-- Form for setting a photo as default -->
        <form id="set-default-photo-form" method="POST">
            <input type="hidden" name="photo_id" id="set-default-photo-id">
            <div id="new-default-photos" style="display:none;">
                <h3>Select a new default photo</h3>
                <select name="new_default_photo_id" id="new_default_photo_id">
                    <?php foreach ($photos as $photo): ?>
                        <option value="<?= $photo->product_photo_id ?>"><?= $photo->product_photo_id ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="actions">
                <button type="submit" name="set_default" class="set-default-btn">Set as Default</button>
            </div>
        </form>

        <button type="button" class="close-btn" onclick="closePopup()">Close</button>
    </div>
</div>






<script>
let currentPhotoId = null;

function showPopup(photoId, isDefault) {
    currentPhotoId = photoId;
    document.getElementById('delete-photo-id').value = photoId;
    document.getElementById('set-default-photo-id').value = photoId;

    // Hide "Set as Default" form if the current photo is already the default
    if (isDefault) {
        document.getElementById('set-default-photo-form').style.display = 'none'; // Hide Set as Default form
    } else {
        document.getElementById('set-default-photo-form').style.display = 'block'; // Show Set as Default form
    }

    // Show the upload form for new photos
    document.getElementById('add_new_photos_form').style.display = 'block';

    // Open the popup
    document.getElementById('popup').style.display = 'flex';
}

// Close the popup
function closePopup() {
    document.getElementById('popup').style.display = 'none';
}

document.getElementById("add_new_photos_form").addEventListener("submit", function (event) {
    const fileInput = document.getElementById("add_photos_id");
    const messageContainer = document.getElementById("photo-message");
    const currentPhotoCount = <?= $photo_count; ?>; // Dynamically insert current photo count from the server
    const selectedFilesCount = fileInput.files.length;
    const maxPhotoCount = 3;

    // Reset message
    messageContainer.style.display = "none";
    messageContainer.textContent = "";

    // Case 1: No files selected
    if (selectedFilesCount === 0) {
        event.preventDefault();
        messageContainer.style.display = "block";
        messageContainer.textContent = "Please select at least one photo before .";
        
        return;
    }

    // Case 2: Exceeding the photo limit
    if (currentPhotoCount + selectedFilesCount > maxPhotoCount) {
        event.preventDefault();
        messageContainer.style.display = "block";
        messageContainer.textContent = `You can only upload up to ${maxPhotoCount} photos. Please reduce the number of selected files.`;
        
        return;
    }

    // If all checks pass, form submission proceeds
});


</script> 