<link rel="stylesheet" href="/css/flash_msg.css">
<?php
include('_admin_head.php');
require_once '../lib/SimplePager.php';

//Superadmin
auth('Admin', 'Superadmin');

$page = req('page', 1);
$p = new SimplePager("SELECT * FROM product ", [], 10, $page);
$_products = $p->result;

$_categories = $_db->query('SELECT * FROM category')->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <link rel="stylesheet" href="/css/admin_management.css">

</head>

<body>
    <div class="container">
        <h1>Product Management</h1>
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
                    <tr>
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

        <!-- Button to Add New Admin and pagination-->
        <div class="pagination-container">
            <button class="btn btn-add" onclick="openModal()">+ Add New Product</button>
            <div class="pagination">
                <?= generateDynamicPagination($p, $sort, $dir); ?>
            </div>
        </div>

        <div id="addAdminModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Add New Product</h2>

                <form method="post" class="form" enctype="multipart/form-data" novalidate>
                    <!-- <label for="id">Id</label>
                    <?= html_text('id', 'maxlength="4" placeholder="P999" data-upper') ?>
                    <?= err('id') ?> -->

                    <label for="category_name">Category</label>
                    <?= html_select('category_name', $_categories) ?>
                    <?= err('category_name') ?>


                    <label for="name">Name</label>
                    <?= html_text('name', 'maxlength="100"') ?>
                    <?= err('name') ?>

                    <label for="price">Price</label>
                    <?= html_number('price', 0.01, 99.99, 0.01) ?>
                    <?= err('price') ?>

                    <label for="photo">Photo</label>
                    <label class="upload" tabindex="0">
                        <?= html_file('photo', 'image/*', 'hidden') ?>
                        <img src="/images/photo.jpg">
                    </label>
                    <?= err('photo') ?>

                    <section>
                        <button>Submit</button>
                        <button type="reset">Reset</button>
                    </section>
                </form>
                <!-- <form id="addAdminForm" method="POST">
                    <label for="admin_name">Admin Name<i class="fas fa-name"></i></label>
                    <div class="input-container">
                        <input type="text" id="admin_name" name="admin_name" placeholder="Enter Name" oninput="this.value = this.value.toUpperCase()" required>
                    </div><br>

                    <label for="adminEmail">Email<i class="fas fa-envelope"></i></label>
                    <div class="input-container">
                        <input type="email" id="adminEmail" name="adminEmail" placeholder="Enter Email" required>
                    </div><br>

                    <label for="adminPassword">Password<i class="fas fa-lock"></i></label>
                    <div class="input-container">
                        <input type="password" id="adminPassword" name="adminPassword" placeholder="Enter Password" required>
                    </div><br>

                    <label for="confirmPassword">Confirm Password<i class="fas fa-lock"></i></label>
                    <div class="input-container">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    </div><br>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-submit">Add Admin</button>
                        <button type="button" class="btn btn-clear" onclick="clearForm()">Clear</button>
                    </div>
                </form> -->
            </div>
        </div>
</body>

</html>