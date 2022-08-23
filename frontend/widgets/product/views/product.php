<?php
/* @var $product array*/


use coderius\swiperslider\SwiperSlider;

$images = [];
$i = 0;
foreach($product['images'] as $img) {
    $i++;
    $images[] = "<img class='product__image' src='$img'>";
    if ($i > 5) break;
}


?>

<div class="product">

    <?= \coderius\swiperslider\SwiperSlider::widget([
        // 'on ' . \coderius\swiperslider\SwiperSlider::EVENT_AFTER_REGISTER_DEFAULT_ASSET => function(){
        //     CustomAsset::register($view);
        // },
        'showScrollbar' => false,
        'slides' => $images,
        'clientOptions' => [
            'slidesPerView' => 1,
            'spaceBetween'=> 30,
            'centeredSlides'=> true,
            'pagination' => [
                'clickable' => true,
                'renderBullet' => new \yii\web\JsExpression("function (index, className) {
                    return '<span class=\"' + className + '\">' + (index + 1) + '</span>';
                },
            "),
            ],
            "scrollbar" => [
                "el" => \coderius\swiperslider\SwiperSlider::getItemCssClass(SwiperSlider::SCROLLBAR),
                "hide" => true,
            ],
        ],

        //Global styles to elements. If create styles for all slides
        'options' => [
            'styles' => [
                \coderius\swiperslider\SwiperSlider::CONTAINER => ["height" => "320px", "width"=>"284px"],
                \coderius\swiperslider\SwiperSlider::SLIDE => ["text-align" => "center"],
            ],
        ],

    ]);

    ?>
    <div class="product-name"><?=$product['name'] ?? ''?></div>
    <div class="product-price"><?=$product['price'] ?? ''?></div>
    <?php
        if (!empty($product['wb_url'])){
            ?>
            <a class="product-link" href="<?=$product['wb_url'] ?? ''?>" target='_blank' ><?=Yii::t("app","на WB")?></a>
            <?php
        }
    ?>
    <?php
    if (!empty($product['ozone_url'])){
        ?>
        <a class="product-link" href="<?=$product['ozone_url'] ?? ''?>" target='_blank' ><?=Yii::t("app","на Ozon")?></a>
        <?php
    }
    ?>

</div>
