<?php
require '_base.php';

// Example member_id for testing
$member_id = "M000001";

// Fetch the product details and cover photo (marked with is_default = true) for each product in the wishlist
$stm = $_db->prepare('
    SELECT p.product_id, p.description, p.unit_price, p.category_name, 
           pp.product_photo_id
    FROM wishlist w
    JOIN product p ON w.product_id = p.product_id
    LEFT JOIN product_photo pp ON p.product_id = pp.product_id AND pp.default_photo = true
    WHERE w.member_id = ?
');
$stm->execute([$member_id]);
$wishlist_items = $stm->fetchAll(PDO::FETCH_ASSOC);

// Display the wishlist
$_title = 'My Wishlist';
include '_head.php';
?>

<link rel="stylesheet" type="text/css" media="screen" href="/css/menu.css" />
<script src="/js/menu.js" defer></script>
<script src="/js/slider.js" defer></script>

<style>

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    table th, table td {
        padding: 12px;
        text-align: left;
    }

    table th {
        background-color: #f8f8f8;
        color: #333;
    }

    table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .item-image img {
        width: 80px;
        height: auto;
        object-fit: cover;
        border-radius: 5px;
    }

    .remove-btn {
        text-decoration: none;
        background-color:transparent;
        color: #C62828;
        padding: 8px 15px;
        font-size: 14px;
    }

    .remove-btn:hover {
        color: #777;
    }

    .remove-btn:active {
        color: #777;
    }


    .no-items {
        text-align: center;
        color: #777;
        font-size: 16px;
    }

</style>

<div class="container">
<a href="javascript:history.back()" class="back-button">
<i class="fa-solid fa-arrow-left-long"></i></a>

    <section class="main">
        <h2>My Wishlist</h2>

        <?php if (empty($wishlist_items)): ?>
            <p class="no-items">Your wishlist is empty.</p>
        <?php else: ?>
            <table>
    <tbody>
    <?php foreach ($wishlist_items as $item): ?>
    <tr>
        <td class="item-image">
            <?php if ($item['product_photo_id']): ?>
                <a href="product_card.php?product_id=<?= $item['product_id'] ?>">
                    <img src="/product_gallery/<?= $item['product_photo_id'] ?>" alt="<?= $item['description'] ?>" />
                </a>
            <?php else: ?>
                <a href="product_card.php?product_id=<?= $item['product_id'] ?>">
                    <img src="/product_gallery/default.jpg" alt="<?= $item['description'] ?>" />
                </a>
            <?php endif; ?>
        </td>
        <td>
            <a href="product_card.php?product_id=<?= $item['product_id'] ?>"><?= $item['description'] ?></a>
        </td>
        <td><?= $item['category_name'] ?></td>
        <td>RM <?= $item['unit_price'] ?></td>
        <td>
            <a href="javascript:void(0);" class="remove-btn" data-product-id="<?= $item['product_id'] ?>">
                <i class="wishlist-icon fa-sharp fa-solid fa-heart" aria-label="Remove from wishlist"></i>
            </a>
        </td>
    </tr>
<?php endforeach; ?>

    </tbody>
</table>

        <?php endif; ?>
    </section>
</div>

<?php
include '_foot.php';
?>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', event => {
            event.preventDefault(); // Prevent default link behavior
            const productId = button.dataset.productId;

            fetch('/wishlist-action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove', // Action for removal
                    product_id: productId,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the item row from the table
                    button.closest('tr').remove();

                    // Check if the table is now empty
                    if (document.querySelectorAll('tbody tr').length === 0) {
                        document.querySelector('.main').innerHTML =
                            '<p class="no-items">Your wishlist is empty.</p>';
                    }
                } else {
                    alert(data.error || 'Error removing item.');
                }
            })
            .catch(() => {
                alert('An unexpected error occurred.');
            });
        });
    });
});

</script>
