<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%wb_items}}".
 *
 * @property int $id
 * @property string $supplierId
 * @property string $wb_id
 * @property string $ip
 * @property int $imtId
 * @property int $nmId
 * @property int $chrtId
 * @property string $size
 * @property string $supplierVendorCode
 * @property string $vendorCode
 * @property string $object
 * @property string $parent
 * @property string $brand
 * @property string $title
 * @property string $description
 * @property string $barcode
 * @property string $barcode2
 * @property string $params
 * @property int $width
 * @property int $height
 * @property int $depth
 * @property int $fbs_stocks
 * @property int $fbs_stocks_rostov
 * @property string $price_change_reason
 */
class WbItems extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wb_items}}';
    }

    private static function getCacheTime()
    {
        if (!isset(Yii::$app->params['wb-items.cacheTime'])) {
            Yii::warning("нет ключа wb-items.cacheTime");
        }
        return Yii::$app->params['wb-items.cacheTime'] ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supplierId', 'wb_id', 'ip', 'imtId', 'nmId', 'chrtId', 'size', 'supplierVendorCode', 'vendorCode', 'object', 'parent', 'brand', 'title', 'description', 'barcode', 'barcode2', 'params', 'width', 'height', 'depth', 'fbs_stocks', 'fbs_stocks_rostov', 'price_change_reason'], 'required'],
            [['imtId', 'nmId', 'chrtId', 'width', 'height', 'depth', 'fbs_stocks', 'fbs_stocks_rostov'], 'integer'],
            [['supplierId', 'wb_id', 'size', 'supplierVendorCode', 'vendorCode'], 'string', 'max' => 64],
            [['ip'], 'string', 'max' => 32],
            [['object', 'parent', 'brand', 'title', 'barcode', 'barcode2'], 'string', 'max' => 128],
            [['description', 'price_change_reason'], 'string', 'max' => 1024],
            [['params'], 'string', 'max' => 4096],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'supplierId' => Yii::t('app', 'Supplier ID'),
            'wb_id' => Yii::t('app', 'Wb ID'),
            'ip' => Yii::t('app', 'Ip'),
            'imtId' => Yii::t('app', 'Imt ID'),
            'nmId' => Yii::t('app', 'Nm ID'),
            'chrtId' => Yii::t('app', 'Chrt ID'),
            'size' => Yii::t('app', 'Size'),
            'supplierVendorCode' => Yii::t('app', 'Supplier Vendor Code'),
            'vendorCode' => Yii::t('app', 'Vendor Code'),
            'object' => Yii::t('app', 'Object'),
            'parent' => Yii::t('app', 'Parent'),
            'brand' => Yii::t('app', 'Brand'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'barcode' => Yii::t('app', 'Barcode'),
            'barcode2' => Yii::t('app', 'Barcode 2'),
            'params' => Yii::t('app', 'Params'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'depth' => Yii::t('app', 'Depth'),
            'fbs_stocks' => Yii::t('app', 'Fbs Stocks'),
            'fbs_stocks_rostov' => Yii::t('app', 'Fbs Stocks Rostov'),
            'price_change_reason' => Yii::t('app', 'Price Change Reason'),
        ];
    }
    static public function getCacheNameByBarcode($barcode){
        return "wb-" . $barcode;
    }

    /** Получение WbItems по баркоду с кэшированием
     * @param $barcode
     * @return WbItems|null
     */
    public static function getOrSetByBarcode($barcode){
        if (!empty($barcode)) {
            return \Yii::$app->cache->getOrSet(self::getCacheNameByBarcode($barcode), function ($cache) use ($barcode) {
                return WbItems::find()
                    ->where(['barcode' => $barcode])
                    ->orWhere(['barcode2' => $barcode])
                    ->one();
            }, self::getCacheTime());
        } else {
            return null;
        }
    }
}
