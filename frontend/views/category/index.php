<?php
/* @var $this yii\web\View */
/* @var $category \common\models\Category */
/* @var $products array */

?>

<h1><?=$category->name?></h1>

<?= \frontend\widgets\product\ProductListWidget::widget([
   'products' => $products
]);

