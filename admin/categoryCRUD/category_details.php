<?php
include '../_base.php';

// Fetch all categories
// $stm = $_db->prepare('SELECT category_id, category_name, sub_category, category_photo, currentStock, minStock, StockAlert, Status FROM category');
// $stm->execute();
// $categories = $stm->fetchAll(PDO::FETCH_ASSOC);

// Get the product ID from the query string
$category_id = req('category_id');

// Fetch the product details
$stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
$stm->execute([$category_id]);
$category = $stm->fetch();


// Set the page title
$_title = 'Category Details';
include '../_head.php';
?>

<div class="category-details-container">
    <?php if ($category): ?>
        <div class="category-card">
            <div class="category-photo-container">
                <img src="../image/<?= htmlspecialchars($category->category_photo) ?>" alt="Category Photo" class="category-photo">
            </div>
            <div class="category-info">
                <h2 class="category-name"><?= htmlspecialchars($category->category_name) ?></h2>
                <p><strong>Category ID:</strong> <?= htmlspecialchars($category->category_id) ?></p>
                <p><strong>Subcategory:</strong> <?= htmlspecialchars($category->sub_category ?: 'N/A') ?></p>
                <p><strong>Current Stock:</strong> <?= htmlspecialchars($category->currentStock) ?></p>
                <p><strong>Minimum Stock:</strong> <?= htmlspecialchars($category->minStock) ?></p>
                <p><strong>Stock Alert:</strong> <?= $category->StockAlert? 'Yes' : 'No' ?></p>
                <p>
                    <strong>Status:</strong> 
                    <span class="<?= $category->Status ? 'status-active' : 'status-inactive' ?>">
                        <?= $category->Status ? 'Active' : 'Inactive' ?>
                    </span>
                </p>
            </div>
        </div>
    <?php else: ?>
        <p class="no-category-message">Category not found.</p>
    <?php endif; ?>
</div>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .category-details-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
        background-color: #f4f4f9;
    }

    .category-card {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 20px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .category-photo-container {
        width: 100%;
        max-width: 300px;
        margin-bottom: 20px;
    }

    .category-photo {
        width: 100%;
        max-width: 100%;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .category-info {
        text-align: left;
        width: 100%;
    }

    .category-info p {
        font-size: 16px;
        margin: 10px 0;
    }

    .category-name {
        font-size: 24px;
        font-weight: bold;
        color: #3f72af;
        margin-bottom: 20px;
        text-align: center;
    }

    .status-active {
        color: #28a745;
        font-weight: bold;
    }

    .status-inactive {
        color: #dc3545;
        font-weight: bold;
    }

    .no-category-message {
        text-align: center;
        font-size: 18px;
        color: #888;
    }

    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .category-card {
            padding: 15px;
        }

        .category-info p {
            font-size: 14px;
        }

        .category-name {
            font-size: 20px;
        }
    }
</style>

<?php
include '../_foot.php';
?>