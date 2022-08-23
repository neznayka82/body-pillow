<?php

/** @var $products array */


?>

<div class="container">
    <div class="row">
        <div class="product-list">
            <?php
            foreach ($products as $product){
                if (count($product['images']) == 0) {
                    $product['images'][] = \common\models\WbImages::noImgUrl();
                }
                echo \frontend\widgets\product\ProductWidget::widget([
                    'product' => $product
                ]);
            }
            ?>
        </div>
    </div>
</div>
