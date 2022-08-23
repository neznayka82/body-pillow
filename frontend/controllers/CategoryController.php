<?php

namespace frontend\controllers;

use common\models\Category;
use common\models\Items1c;
use Yii;
use yii\web\NotFoundHttpException;

class CategoryController extends \yii\web\Controller
{
    public function actionIndex($slug)
    {
        Yii::debug($slug, "category");
        //проверяем что строка заканчивается на /
        if (!str_ends_with($slug,"/" )){
            $slug .="/";
            $this->redirect("/". $slug);
        }

        //Получаем категорию
        $c = Category::getOrSetByPath($slug);
        //Получаем продукты в категории
        if (!isset($c)){
            throw new NotFoundHttpException("нет такой страницы");
        }

        if ($c->parent_id == 0){
            $cats = $c->getChilds();
            Yii::debug(count($cats));
            $p = [];
            foreach ($cats as $c_item){
                $p = array_merge($p, Items1c::getProducts($c_item));
            }
        } else {
            $p = Items1c::getProducts($c);//Product::getByCategory($c);
        }

        return $this->render('index', [
            'category' => $c,
            'products' => $p
        ]);
    }

}
