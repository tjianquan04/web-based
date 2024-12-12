<?php
include '../_base.php';

// Check if category_id is provided
if (!isset($_GET['category_id'])) {
    die("Error: No category ID provided.");
}

$category_id = $_GET['category_id'];

// Retrieve the category details
$stm = $_db->prepare('SELECT * FROM category WHERE category_id = ?');
$stm->execute([$category_id]);
$category = $stm->fetch();

if (!$category) {
    die("Error: Category not found.");
}

// Retrieve the associated products
$product_stm = $_db->prepare('SELECT * FROM product WHERE category_id = ?');
$product_stm->execute([$category_id]);
$products = $product_stm->fetchAll();

// Handle deletion if user confirms
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $_db->beginTransaction();

        // Delete associated products
        $delete_products_stm = $_db->prepare('DELETE FROM product WHERE category_id = ?');
        $delete_products_stm->execute([$category_id]);

        // Delete the category
        $delete_category_stm = $_db->prepare('DELETE FROM category WHERE category_id = ?');
        $delete_category_stm->execute([$category_id]);

        // Delete associated photo if exists
        $photo_path = '../image/' . $category->category_photo;
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }

        $_db->commit();

        // Redirect with success message
        temp('info', 'Category successfully deleted.');
            redirect('viewCategory.php');
        exit;
    } catch (Exception $e) {
        $_db->rollBack();
        die("Error: Unable to delete category. " . $e->getMessage());
    }
}

$_title = 'Category Management | DELETE';
include '../_head.php';
?>

<h2>Confirm Deletion</h2>
<p>Are you sure you want to delete the following category and its associated products?</p>

<h3>Category Details:</h3>
<ul>
    <li><strong>ID:</strong> <?= htmlspecialchars($category->category_id) ?></li>
    <li><strong>Name:</strong> <?= htmlspecialchars($category->category_name) ?></li>
    <li><strong>Subcategory:</strong> <?= htmlspecialchars($category->sub_category) ?></li>
    <li><strong>Photo:</strong> <img src="../image/<?= htmlspecialchars($category->category_photo) ?>" alt="Category Photo" width="100"></li>
</ul>

<h3>Associated Products:</h3>
<?php if (count($products) > 0): ?>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>#</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $index => $product): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($product->product_id) ?></td>
                    <td><?= htmlspecialchars($product->description) ?></td>
                    <td><?= htmlspecialchars($product->unit_price) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No products are associated with this category.</p>
<?php endif; ?>

<form method="POST">
    <button type="submit" name="confirm_delete" style="background-color: red; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Confirm Delete</button>
    <a href="viewCategory.php" style="margin-left: 10px; padding: 10px 15px; background-color: gray; color: white; text-decoration: none; border-radius: 4px;">Cancel</a>
</form>

<?php include '../_foot.php'; ?>
