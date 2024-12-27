<?php
include '../_base.php';
auth('Superadmin', 'Product Manager');

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

          // Delete associated photo if exists
          $photo_path = '../image/' . $category->category_photo;
          if (file_exists($photo_path)) {
              unlink($photo_path);
          }

          foreach ($products as $product) {
            // Delete photos from the product_photo table
            $delete_photos_stm = $_db->prepare('DELETE FROM product_photo WHERE product_id = ?');
            $delete_photos_stm->execute([$product->product_id]);
        }
      // Update category status to "Discontinued" and set dateDeleted
$update_category_stm = $_db->prepare('UPDATE category SET status = "Discontinued", dateDeleted = NOW(), currentStock = 0 , StockAlert = false WHERE category_id = ?');
$update_category_stm->execute([$category_id]);

// Update the associated products status to "Discontinued" and set dateDeleted
foreach ($products as $product) {
    $update_product_stm = $_db->prepare('UPDATE product SET status = "Discontinued", dateDeleted = NOW(), stock_quantity=0 WHERE product_id = ?');
    $update_product_stm->execute([$product->product_id]);
}

        $_db->commit();

        // Redirect with success message
        temp('info', 'Category have been updated to discontinued.');
            redirect('viewCategory.php');
        exit;
    } catch (Exception $e) {
        $_db->rollBack();
        die("Error: Unable to delete category. " . $e->getMessage());
    }
}

$_title = 'Category Management | DELETE';

?>

<style>

        .delete {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #dc3545;
            font-size: 24px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }

        h3 {
            color: #333;
            font-size: 20px;
            margin-top: 30px;
        }

        p {
            color: #555;
            line-height: 1.6;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            margin-bottom: 10px;
            color: #333;
        }

        img {
            display: block;
            margin-top: 10px;
            border-radius: 5px;
            max-height: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            margin: 5px;
            text-align: center;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        form {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        button {
            background-color: #dc3545;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background-color: #c82333;
        }

        .link {
            margin-left: 10px;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .link:hover {
            background-color: #5a6268;
        }
    </style>

<div class="delete">
<a href="javascript:history.back()" class="back-button">&larr;</a>

        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete the following category and its associated products?</p>

        <h3>Category Details:</h3>
        <ul>
            <li><strong>ID:</strong> <?= htmlspecialchars($category->category_id) ?></li>
            <li><strong>Name:</strong> <?= htmlspecialchars($category->category_name) ?></li>
            <li><strong>Subcategory:</strong> <?= htmlspecialchars($category->sub_category) ?></li>
            <li><strong>Photo:</strong> <img src="../image/<?= htmlspecialchars($category->category_photo) ?>" alt="Category Photo"></li>
        </ul>

        <h3>Associated Products:</h3>
        <?php if (count($products) > 0): ?>
            <table>
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
            <button type="submit" name="confirm_delete">Confirm Delete</button>
            <a href="viewCategory.php" class="link">Cancel</a>
        </form>
    </div>

