<?php

use common\models\Category;

/** @var $category Category */


?>

<a href="/<?=$category->t_path?>" class="category-item">
    <div class="category-item__name"><?=$category->name?></div>
    <div class="category-item__count"><?=$category->count?></div>
</a>

