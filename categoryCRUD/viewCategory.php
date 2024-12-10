<?php
include '../_base.php';

// ----------------------------------------------------------------------------

$_categories = $_db->query('SELECT * FROM category')->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Category Management';
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



<p><?= count($_categories) ?> record(s)</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Subcategory</th>
            <th>Photo</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $numofcategories = 1; ?>
        <?php foreach ($_categories as $_category): ?>
            <tr onclick="window.location.href='product_details.php?product_id=<?= $_category->category_id ?>'">
                <td><?= $numofcategories++ ?></td>
                <td><?= $_category->category_id ?></td>
                <td class="description-cell"><?= $_category->category_name ?></td>
                <td><?= $_category->sub_category ?></td>
                <td><img src="../image/<?= ($_category->category_photo) ?>" alt="Category Photo" class="category-photo">
            
                <td>
                    <a href='#' class='btn btn-edit'><i class='fas fa-tools'></i>Edit</a>
                    <a href='#' class='btn btn-delete' onclick='return confirm("Are you sure you want to delete this Category?")'>
                        <i class='fas fa-trash-alt'></i>Delete</a>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
    </table>

    <a href="category_insert.php"><button>Add new category</button></a>

    <?php
    include '../_foot.php';
