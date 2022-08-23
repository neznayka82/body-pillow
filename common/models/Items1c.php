<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%1c_items}}".
 *
 * @property int $id
 * @property string $Ref_Key
 * @property string $Parent_Key
 * @property string $Code
 * @property string $Description
 * @property string $Article
 * @property string $TitleFull
 * @property string|null $wb_size
 * @property string|null $wb_category
 * @property string|null $wb_brand
 * @property string|null $wb_barcode_x
 * @property string|null $wb_barcode_add
 * @property string|null $wb_barcode
 * @property string|null $wb_article_x
 * @property string|null $wb_article_color_x
 * @property string|null $wb_article_color
 * @property string|null $wb_article
 * @property string|null $ozon_title
 * @property string|null $ozon_id_fbs
 * @property string|null $ozon_id
 * @property string|null $ozon_barcode
 * @property string|null $ozon_article
 * @property string|null $ds_title
 * @property string|null $ds_barcode
 * @property string|null $ds_article
 * @property string|null $beru_title
 * @property string|null $beru_sku_fbs
 * @property string|null $beru_sku
 * @property string|null $beru_barcode
 * @property string|null $aku_title
 * @property string|null $aku_barcode
 * @property string|null $aku_article
 * @property string|null $1c_limit
 * @property int|null $1c_limit_total
 * @property string|null $ostatok_type
 * @property string|null $wb_stocks_auto_date
 * @property int|null $cost
 * @property int|null $cost_sborka
 * @property float $cost_poshiv
 * @property string|null $Ed_Izm
 * @property int|null $capacity_93 кол-во в коробе 93 см
 * @property int|null $capacity_6040 кол-во в коробе 60х40 см
 * @property int|null $capacity_4030 кол-во в коробе 40х30 см
 * @property string|null $sebes_dt
 */
class Items1c extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%1c_items}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Ref_Key', 'Parent_Key', 'Code', 'Description', 'Article', 'TitleFull'], 'required'],
            [['1c_limit_total', 'cost', 'cost_sborka', 'capacity_93', 'capacity_6040', 'capacity_4030'], 'integer'],
            [['wb_stocks_auto_date', 'sebes_dt'], 'safe'],
            [['cost_poshiv'], 'number'],
            [['Ref_Key', 'Parent_Key', 'Article', 'wb_size', 'wb_category', 'wb_brand', 'wb_barcode_x', 'wb_barcode_add', 'wb_barcode', 'wb_article_x', 'wb_article_color_x', 'wb_article_color', 'wb_article', 'ozon_title', 'ozon_id_fbs', 'ozon_id', 'ozon_barcode', 'ozon_article', 'ds_title', 'ds_barcode', 'ds_article', 'beru_sku_fbs', 'beru_sku', 'beru_barcode', 'aku_title', 'aku_barcode', 'aku_article', '1c_limit'], 'string', 'max' => 128],
            [['Code', 'ostatok_type', 'Ed_Izm'], 'string', 'max' => 32],
            [['Description', 'TitleFull', 'beru_title'], 'string', 'max' => 256],
            [['Ref_Key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'Ref_Key' => Yii::t('app', 'Ref Key'),
            'Parent_Key' => Yii::t('app', 'Parent Key'),
            'Code' => Yii::t('app', 'Code'),
            'Description' => Yii::t('app', 'Description'),
            'Article' => Yii::t('app', 'Article'),
            'TitleFull' => Yii::t('app', 'Title Full'),
            'wb_size' => Yii::t('app', 'Wb Size'),
            'wb_category' => Yii::t('app', 'Wb Category'),
            'wb_brand' => Yii::t('app', 'Wb Brand'),
            'wb_barcode_x' => Yii::t('app', 'Wb Barcode X'),
            'wb_barcode_add' => Yii::t('app', 'Wb Barcode Add'),
            'wb_barcode' => Yii::t('app', 'Wb Barcode'),
            'wb_article_x' => Yii::t('app', 'Wb Article X'),
            'wb_article_color_x' => Yii::t('app', 'Wb Article Color X'),
            'wb_article_color' => Yii::t('app', 'Wb Article Color'),
            'wb_article' => Yii::t('app', 'Wb Article'),
            'ozon_title' => Yii::t('app', 'Ozon Title'),
            'ozon_id_fbs' => Yii::t('app', 'Ozon Id Fbs'),
            'ozon_id' => Yii::t('app', 'Ozon ID'),
            'ozon_barcode' => Yii::t('app', 'Ozon Barcode'),
            'ozon_article' => Yii::t('app', 'Ozon Article'),
            'ds_title' => Yii::t('app', 'Ds Title'),
            'ds_barcode' => Yii::t('app', 'Ds Barcode'),
            'ds_article' => Yii::t('app', 'Ds Article'),
            'beru_title' => Yii::t('app', 'Beru Title'),
            'beru_sku_fbs' => Yii::t('app', 'Beru Sku Fbs'),
            'beru_sku' => Yii::t('app', 'Beru Sku'),
            'beru_barcode' => Yii::t('app', 'Beru Barcode'),
            'aku_title' => Yii::t('app', 'Aku Title'),
            'aku_barcode' => Yii::t('app', 'Aku Barcode'),
            'aku_article' => Yii::t('app', 'Aku Article'),
            '1c_limit' => Yii::t('app', '1c Limit'),
            '1c_limit_total' => Yii::t('app', '1c Limit Total'),
            'ostatok_type' => Yii::t('app', 'Ostatok Type'),
            'wb_stocks_auto_date' => Yii::t('app', 'Wb Stocks Auto Date'),
            'cost' => Yii::t('app', 'Cost'),
            'cost_sborka' => Yii::t('app', 'Cost Sborka'),
            'cost_poshiv' => Yii::t('app', 'Cost Poshiv'),
            'Ed_Izm' => Yii::t('app', 'Ed Izm'),
            'capacity_93' => Yii::t('app', 'Capacity  93'),
            'capacity_6040' => Yii::t('app', 'Capacity  6040'),
            'capacity_4030' => Yii::t('app', 'Capacity  4030'),
            'sebes_dt' => Yii::t('app', 'Sebes Dt'),
        ];
    }

    public static function getProducts(Category $category){
        $result = [];
        try{
            $items1c = Items1c::find()
                ->where(['Parent_Key' => $category->Ref_Key])
                ->all();

            foreach($items1c as $item){
                /* @var $item Items1c */
                $i = [];
                $i['name'] = $item->TitleFull;
                $wb_item = WbItems::getOrSetByBarcode($item->wb_barcode);
                $wb_item2 =WbItems::getOrSetByBarcode($item->wb_barcode_add);
                $wb_item3 =WbItems::getOrSetByBarcode($item->wb_barcode_add);
                if (isset($wb_item)) {
                    $i['wb_item'] = $wb_item;
                } elseif(isset($wb_item2)) {
                    $i['wb_item'] = $wb_item2;
                } elseif(isset($wb_item3)){
                    $i['wb_item'] = $wb_item2;
                } else {
                    continue;
                }

                $i['images'] = [];
                if (isset($i['wb_item'])) {
                    $images = WbImages::getOrSetBynmId($i['wb_item']->nmId);
                    foreach($images as $img){
                        //if ($img->ya_ignore == 1) continue;
                        $i['images'] []= $img->getUrl();
                    }
                    $i['wb_url'] = "https://www.wildberries.ru/catalog/" . $i['wb_item']->nmId . "/detail.aspx?targetUrl=SP";
                }

                $oi = OzonItem::getOrSetByBarcode($item->ozon_barcode);
                if (isset($oi) && !empty($oi->sku_fbs)){
                    $i['ozone_url'] = "https://www.ozon.ru/context/detail/id/" . $oi->sku_fbs;
                }


                $result [] = $i;
            }
        } catch(\Exception $e){
            $msg = $e->getMessage();
            Yii::error($msg);
        }
        return $result;
    }
}
