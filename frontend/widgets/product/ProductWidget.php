<?php


namespace frontend\widgets\product;


use yii\bootstrap4\Widget;

class ProductWidget extends Widget
{

    public $product;

    public function run()
    {
        return $this->render("product", [
            'product' => $this->product
        ]);
    }
}