<?php
require '../_base.php';

// ----------------------------------------------------------------------------

$_products = $_db->query('SELECT * FROM product')->fetchAll();

$name = req('name'); // Search keyword

if ($name) {
    // Fetch categories based on the search keyword
    $stm = $_db->prepare('SELECT * FROM product WHERE description LIKE ? ');
    $stm->execute(["%$name%"]);
    $_products = $stm->fetchAll();
    
} else {
    // Fetch all categories if no search keyword is provided
    $_products = $_db->query('SELECT * FROM product')->fetchAll();
}

// ----------------------------------------------------------------------------

$_title = 'Product | Index';
include '../_head.php';
?>

<style>
/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 18px;
    text-align: left;
}

thead {
    background-color: #f4f4f4;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
}

tr:hover {
    background-color: #f0f0f0;
    cursor: pointer;
}

td .btn {
    display: inline-block;
    margin: 0 5px;
    padding: 8px 12px;
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    border-radius: 4px;
}

.btn-edit {
    background-color: #007bff;
}

.btn-edit:hover {
    background-color: #0056b3;
}

.btn-delete {
    background-color: #dc3545;
}

.btn-delete:hover {
    background-color: #c82333;
}

.btn-add {
    background-color:rgb(108, 235, 137);
    color: #fff;
    text-decoration: none;
    padding: 10px 15px;
    font-size: 16px;
    border-radius: 5px;
    display: inline-block;

}

.btn-add:hover {
    background-color: #218838;
}
</style>

<a href="addProduct.php" class="btn-add"><i class="fas fa-plus-circle"></i> Add New Product</a>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product ID</th>
            <th>Description</th>
            <th>Category</th>
            <th>Unit Price</th>
            <th>Stock</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $numofproduct = 1; ?>
        <?php foreach ($_products as $product): ?>
            <tr onclick="window.location.href='product_details.php?product_id=<?= $product->product_id ?>'">
                <td><?= $numofproduct++ ?></td>
                <td><?= $product->product_id ?></td>
                <td class="description-cell"><?= $product->description ?></td>
                <td><?= $product->category_name ?></td>
                <td><?= $product->unit_price ?></td>
                <td><?= $product->stock_quantity ?></td>
                <td>
                    <a href="#" class="btn btn-edit"><i class="fas fa-tools"></i> Edit</a>
                    <form action="deleteProduct.php" method="POST" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?= $product->product_id ?>">
                        <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this product?')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <?php
    include '../_foot.php';


    