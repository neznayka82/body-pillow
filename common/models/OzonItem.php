<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%ozon_items}}".
 *
 * @property string $ip
 * @property int $id
 * @property string $name
 * @property string $offer_id
 * @property string $barcode
 * @property int $category_id
 * @property int $marketing_price
 * @property int $old_price
 * @property int $premium_price
 * @property int $price
 * @property int $recommended_price
 * @property int $sku_fbo
 * @property int $sku_fbs
 * @property int $stocks_present
 * @property int $stocks_reserved
 * @property float $commissions_fbo_percent
 * @property float $commissions_fbo_value
 * @property float $commissions_fbs_percent
 * @property float $commissions_fbs_value
 * @property float $commissions_rfbs_percent
 * @property float $commissions_rfbs_value
 * @property float $volume_weight
 * @property float $price_index
 * @property int $depth
 * @property int $height
 * @property float $weight
 * @property string $dimension_unit
 * @property string $ts
 * @property int $is_archive 0 - не в архиве 1 - в архиве
 */
class OzonItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ozon_items}}';
    }

    private static function getCacheTime()
    {
        if (!isset(Yii::$app->params['ozone-items.cacheTime'])) {
            Yii::warning("нет ключа ozone-items.cacheTime");
        }
        return Yii::$app->params['ozone-items.cacheTime'] ?? 0;
    }

    private static function getCacheNameByBarcode($barcode)
    {
        return "ozone-items-" . $barcode;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip', 'id', 'name', 'offer_id', 'barcode', 'category_id', 'marketing_price', 'old_price', 'premium_price', 'price', 'recommended_price', 'sku_fbo', 'sku_fbs', 'stocks_present', 'stocks_reserved', 'commissions_fbo_percent', 'commissions_fbo_value', 'commissions_fbs_percent', 'commissions_fbs_value', 'commissions_rfbs_percent', 'commissions_rfbs_value', 'volume_weight', 'price_index', 'depth', 'height', 'weight', 'dimension_unit'], 'required'],
            [['id', 'category_id', 'marketing_price', 'old_price', 'premium_price', 'price', 'recommended_price', 'sku_fbo', 'sku_fbs', 'stocks_present', 'stocks_reserved', 'depth', 'height', 'is_archive'], 'integer'],
            [['commissions_fbo_percent', 'commissions_fbo_value', 'commissions_fbs_percent', 'commissions_fbs_value', 'commissions_rfbs_percent', 'commissions_rfbs_value', 'volume_weight', 'price_index', 'weight'], 'number'],
            [['ts'], 'safe'],
            [['ip'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 256],
            [['offer_id', 'barcode'], 'string', 'max' => 64],
            [['dimension_unit'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ip' => Yii::t('app', 'Ip'),
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'barcode' => Yii::t('app', 'Barcode'),
            'category_id' => Yii::t('app', 'Category ID'),
            'marketing_price' => Yii::t('app', 'Marketing Price'),
            'old_price' => Yii::t('app', 'Old Price'),
            'premium_price' => Yii::t('app', 'Premium Price'),
            'price' => Yii::t('app', 'Price'),
            'recommended_price' => Yii::t('app', 'Recommended Price'),
            'sku_fbo' => Yii::t('app', 'Sku Fbo'),
            'sku_fbs' => Yii::t('app', 'Sku Fbs'),
            'stocks_present' => Yii::t('app', 'Stocks Present'),
            'stocks_reserved' => Yii::t('app', 'Stocks Reserved'),
            'commissions_fbo_percent' => Yii::t('app', 'Commissions Fbo Percent'),
            'commissions_fbo_value' => Yii::t('app', 'Commissions Fbo Value'),
            'commissions_fbs_percent' => Yii::t('app', 'Commissions Fbs Percent'),
            'commissions_fbs_value' => Yii::t('app', 'Commissions Fbs Value'),
            'commissions_rfbs_percent' => Yii::t('app', 'Commissions Rfbs Percent'),
            'commissions_rfbs_value' => Yii::t('app', 'Commissions Rfbs Value'),
            'volume_weight' => Yii::t('app', 'Volume Weight'),
            'price_index' => Yii::t('app', 'Price Index'),
            'depth' => Yii::t('app', 'Depth'),
            'height' => Yii::t('app', 'Height'),
            'weight' => Yii::t('app', 'Weight'),
            'dimension_unit' => Yii::t('app', 'Dimension Unit'),
            'ts' => Yii::t('app', 'Ts'),
            'is_archive' => Yii::t('app', 'Is Archive'),
        ];
    }

    /**
     * @param $barcode
     * @return OzonItem|null
     */
    public static function getOrSetByBarcode($barcode){
        if (!empty($barcode)) {
            return \Yii::$app->cache->getOrSet(self::getCacheNameByBarcode($barcode), function ($cache) use ($barcode) {
                return OzonItem::find()
                    ->where(['barcode' => $barcode])
                    ->one();
            }, self::getCacheTime());
        } else {
            return null;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete(self::getCacheNameByBarcode($this->barcode));
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }
}
