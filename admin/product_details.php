<?php
include '_admin_head.php';
auth('Superadmin', 'Product Manager');

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

?>

<style>
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

     .back-button {
        text-decoration: none;
        font-size: 1.5em;
        color:rgb(0, 0, 0);
        margin-right: 10px;
        transition: color 0.3s ease;
    }

    .back-button:hover {
        color:rgb(220, 0, 0);
    }

/* Success Message */
.success-messages {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px;
    margin-bottom: 20px;
}

.success-messages ul {
    list-style-type: none;
    padding: 0;
}

.success-messages li {
    margin-bottom: 5px;
}

/* Error Message */
.error-messages {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 10px;
    margin-bottom: 20px;
}

.error-messages ul {
    list-style-type: none;
    padding: 0;
}

.error-messages li {
    margin-bottom: 5px;
}




</style>
<link rel="stylesheet" href="../css/flash_msg.css">
<link rel="stylesheet" href="../css/detailsForm.css">
<script src="../js/main.js"></script>



<div class="container">
<?php if (isset($_SESSION['success'])): ?>
    <div class="success-messages">
        <ul>
            <li><?php echo $_SESSION['success']; ?></li>
        </ul>
    </div>
    <?php unset($_SESSION['success']); ?> <!-- Clear the success message after displaying -->
<?php elseif (isset($_SESSION['errors'])): ?>
    <div class="error-messages">
        <ul>
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><strong>Product updated failed: </strong><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?> <!-- Clear errors after displaying -->
<?php endif; ?>


<div class="product-details">
        <a href="product_index.php" class="back-button">&larr;</a>

    <h1>Product Details</h1>

    <div class="product-photos">
        <h3>Product Photos</h3>
        
            <div class="photo-gallery">
                <?php foreach ($photos as $photo): ?>
                    <a href="product_photo.php?product_id=<?=$product->product_id ?>"><img src="../product_gallery/<?= ($photo->product_photo_id) ?>" alt="Product Photo" class="product-photo"></a>
                <?php endforeach; ?>
            </div>
           
    </div>
    <div class="product-info">
        <table>
        <tr>
                <td class="label"><i class="fas fa-id-card"></i>Product ID</td>
                <td class="value"><?= $product->product_id ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-id-card"></i>Unit Price</td>
                <td class="value">RM <?= number_format($product->unit_price, 2) ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-id-card"></i>Stock Quantity</td>
                <td class="value"><?= $product->stock_quantity ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-id-card"></i>Category</td>
                <td class="value"><?= $product->category_name ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-id-card"></i>Subcategory</td>
                <td class="value"><?= $subcategory['sub_category'] ?: 'None' ?></td>
            </tr>
            <tr>
                <td class="label"><i class="fas fa-id-card"></i>Status</td>
                <td class="value"><?= $product->status ?></td>
            </tr>

        </table>
       
    </div>

    <?php if ($product->status !== 'Discontinued'): ?>
        <button id="editBtn">Edit</button>
    <?php endif; ?>
</div>

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
                <div id="invalidExpMessage" style="color: red; display: none;">Invalid date must be after the current date.</div>

            </div>
            <br><br>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

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

    // Check if the selected limited edition date is after the current date
document.getElementById('limited-edition-date').addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const currentDate = new Date();
    const invalidExpMessage = document.getElementById('invalidExpMessage');

    // Set the time to 00:00:00 to ignore the time part during comparison
    currentDate.setHours(0, 0, 0, 0);

    if (selectedDate <= currentDate) {
        invalidExpMessage.style.display = 'block';
    } else {
        invalidExpMessage.style.display = 'none';
    }
});
</script>