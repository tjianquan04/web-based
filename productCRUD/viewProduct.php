<?php
include '../_base.php';

// ----------------------------------------------------------------------------

$_products = $_db->query('SELECT * FROM product')->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Product | Index';
include '../_head.php';
?>

<style>
    .popup {
        width: 100px;
        height: 100px;
    }

    tr {
        cursor: pointer;
    }

    tr:hover {
        background-color: #f0f0f0;
    }
</style>



<p><?= count($_products) ?> record(s)</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product ID</th>
            <th>Description</th>
            <th>Category</th>
            <th>Unit_price</th>
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
                    <a href='#' class='btn btn-edit'><i class='fas fa-tools'></i>Edit</a>
                    <a href='#' class='btn btn-delete' onclick='return confirm("Are you sure you want to delete this admin?")'>
                        <i class='fas fa-trash-alt'></i>Delete</a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
    </table>

    <a href="product_insert.php"><button>Add new product</button></a>

    <?php
    include '../_foot.php';
