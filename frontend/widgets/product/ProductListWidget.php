<?php


namespace frontend\widgets\product;


use yii\bootstrap4\Widget;

class ProductListWidget extends Widget
{
    public $products;

    public function run()
    {
        return $this->render("product_list",[
            'products' => $this->products
        ]);
    }
}