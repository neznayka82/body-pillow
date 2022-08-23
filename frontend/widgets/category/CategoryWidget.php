<?php


namespace frontend\widgets\category;


use yii\bootstrap4\Widget;

class CategoryWidget extends Widget
{

    public $category;

    public function run(){
        return $this->render("category_item", [
           'category' => $this->category
        ]);
    }
}