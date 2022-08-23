<?php

/** @var $categories Category[] */

use common\models\Category;


?>
<div class="container">
    <div class="row">
        <h2> Список Категорий</h2>
        <div class="category-list">
    <?php
        foreach ($categories as $category){
            if ($category->count == 0) continue;
            echo \frontend\widgets\category\CategoryWidget::widget([
                'category' => $category
            ]);
        }
    ?>
        </div>
    </div>
</div>
