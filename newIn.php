<?php
require '_base.php';

$_title = 'New IN';

include '_head.php';

$stm = $_db->prepare('
    SELECT * FROM product WHERE dateAdded <= DATE_SUB(CURDATE(), INTERVAL 2 WEEK)');
$stm->execute();
$newAdded = $stm->fetchAll(PDO::FETCH_ASSOC);

?>
