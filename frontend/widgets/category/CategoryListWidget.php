<?php


namespace frontend\widgets\category;


use common\models\Category;
use common\models\Category1C;
use yii\bootstrap4\Widget;

class CategoryListWidget extends Widget
{

    private $categories = [];

    public function init()
    {
        $this->categories = Category::getParents();
    }

    public function run()
    {
        return $this->render("category_list", [
           'categories' => $this->categories
        ]);
    }
}