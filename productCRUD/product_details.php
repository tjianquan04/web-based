<?php
require '../_base.php';

// Check if there's a success message in the session
if (isset($_SESSION['success'])) {
    // Display the success message
    temp('info', $_SESSION['success']); // Use the session message directly
    unset($_SESSION['success']); // Remove the success message from session
}

// Check if there are errors in the session
if (isset($_SESSION['errors'])) {
    // Loop through each error and display it
    foreach ($_SESSION['errors'] as $error) {
        temp('info', "Failed to update the product. Reason : " . $error); // Display each error with a prefix
    }
    unset($_SESSION['errors']); // Remove errors from session
}



// Get the product ID from the query string
$product_id = req('product_id');

// Fetch the product details
$stm = $_db->prepare('SELECT * FROM product WHERE product_id = ?');
$stm->execute([$product_id]);
$product = $stm->fetch();

// Fetch all photos for the product
$photo_stm = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ?');
$photo_stm->execute([$product_id]);
$photos = $photo_stm->fetchAll();

// Fetch the subcategory from the database based on category_id
$subcategory_stm = $_db->prepare('SELECT sub_category FROM category WHERE category_id = ?');
$subcategory_stm->execute([$product->category_id]);  // Use $product->category_id
$subcategory = $subcategory_stm->fetch(PDO::FETCH_ASSOC); // Fetch as an associative array

if (!$product) {
    die('Product not found');
}


// Include the header
include '../_head.php';
?>

<style>
    /* Base styles */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f8f8;
        color: #333;
    }

    /* Header styling */
    h1,
    h2,
    h3 {
        color: #333;
    }

    /* Container for the product details */
    .product-details {
        width: 80%;
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Product photo gallery */
    .product-photos {
        margin-bottom: 30px;
    }

    .product-photos h3 {
        font-size: 1.5em;
        margin-bottom: 15px;
    }

    .photo-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .product-photo {
        width: 100%;
        max-width: 250px;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Product info */
    .product-info {
        font-size: 1.1em;
    }

    .product-info p {
        margin-bottom: 10px;
    }

    .product-info strong {
        color: #555;
    }

    /* Price and stock quantity */
    .product-info p strong {
        font-weight: bold;
    }

    .product-info p:last-child {
        font-size: 1.2em;
        font-weight: bold;
        color: #e94e77;
        /* Price color */
    }

    /* Button styling */
    button {
        background-color: #e94e77;
        color: #fff;
        border: none;
        padding: 12px 24px;
        font-size: 1em;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #d43f63;
    }

    button:focus {
        outline: none;
    }

    /* Back button container */
    a {
        text-decoration: none;
        display: inline-block;
        margin-top: 20px;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .product-details {
            width: 90%;
        }

        .photo-gallery {
            flex-direction: column;
            align-items: center;
        }

        .product-photo {
            max-width: 100%;
        }

        .product-info p {
            font-size: 1em;
        }
    }



    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        border-radius: 8px;
        text-align: center;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<div class="product-details">
    <h1>Product Details</h1>

    <div class="product-photos">
        <h3>Product Photos</h3>
        <?php if ($photos): ?>
            <div class="photo-gallery">
                <?php foreach ($photos as $photo): ?>
                    <a href="product_photo.php?product_id=<?= $product->product_id ?>"><img src="../product_gallery/<?= ($photo->product_photo_id) ?>" alt="Product Photo" class="product-photo"></a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No photos available for this product.</p>
        <?php endif; ?>
    </div>
    <div class="product-info">
        <h2><?= $product->description ?></h2>
        <p><strong>Product ID:</strong> <?= $product->product_id ?></p>
        <p><strong>Price:</strong> RM <?= number_format($product->unit_price, 2) ?></p>
        <p><strong>Stock Quantity:</strong> <?= $product->stock_quantity ?></p>

        <p><strong>Category:</strong> <?= $product->category_name ?></p>
        <p><strong>Subcategory:</strong> <?= $subcategory['sub_category'] ?: 'None' ?></p>

        <p><strong>Status:</strong><?= $product->status ?></p>
    </div>


</div>

<a href="viewProduct.php"><button>Back</button></a>
<button id="editBtn">Edit</button>


<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit Product Details</h2>
        <form method="POST" action="product_update.php">
            <input type="hidden" name="product_id" value="<?= $product->product_id ?>">
            <label for="unit_price">Unit Price (RM):</label>
            <input type="number" step="0.01" id="unit_price" name="unit_price" value="<?= $product->unit_price ?>" min="1.00" max="9999.99" required>
            <br><br>
            <label for="stock_quantity">Stock Quantity:</label>
            <input
                type="number"
                id="stock_quantity"
                name="stock_quantity"
                value="<?= htmlspecialchars($product->stock_quantity ?? 0) ?>"
                min="0"
                max="999"
                required>
            <div id="outOfStockMessage" style="color: red; display: none;">Once stock quantity update to 0, status will be updated to Out Of Stock as well.</div>
            <br><br>
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="Active" <?= $product->status == 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $product->status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="LimitedEdition" <?= $product->status == 'LimitedEdition' ? 'selected' : '' ?>>Limited Edition</option>
            </select>

            <div id="date-section" style="display: none; margin-top: 10px;">
                <label for="limited-edition-date">Select Date:</label>
                <input type="date" id="limited-edition-date" name="invalid_date" />
            </div>
            <br><br>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>
<?php
// Include the footer
include '../_foot.php';
?>

<script>
    document.getElementById('editBtn').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'block';
    });

    document.querySelector('.close-btn').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });

    window.onclick = function(event) {
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    };

    // Show or hide date-section based on selected status
    document.getElementById('status').addEventListener('change', function() {
        const dateSection = document.getElementById('date-section');
        if (this.value === 'LimitedEdition') {
            dateSection.style.display = 'block';
        } else {
            dateSection.style.display = 'none';
            document.getElementById('limited-edition-date').value = ''; // Clear the date field when hidden
        }
    });

    // Ensure correct state on initial load
    document.addEventListener('DOMContentLoaded', function() {
        const statusDropdown = document.getElementById('status');
        const dateSection = document.getElementById('date-section');
        if (statusDropdown.value === 'LimitedEdition') {
            dateSection.style.display = 'block';
        } else {
            dateSection.style.display = 'none';
        }
    });

    document.getElementById('stock_quantity').addEventListener('input', function() {
        const stockQuantity = parseInt(this.value, 10);
        const outOfStockMessage = document.getElementById('outOfStockMessage');

        if (stockQuantity === 0) {
            outOfStockMessage.style.display = 'block';
        } else {
            outOfStockMessage.style.display = 'none';
        }
    });
</script>