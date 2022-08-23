<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%wb_images}}".
 *
 * @property int $id
 * @property string $url url источника картинки
 * @property int $upload изображение скачано 0-нет 1-да
 * @property int $nmId id связанного товара по nmId
 
 * @property int $ya_ignore 0- не игнорируем для yandex маркета 1 - игнорируем
 */
class WbImages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wb_images}}';
    }

    public static function getCacheNameBynmId($nmId){
        return "wb-image-" . $nmId;
    }

    /**
     * @param $nmId
     * @return WbImages[]
     */
    public static function getOrSetBynmId($nmId): array
    {
        return \Yii::$app->cache->getOrSet(self::getCacheNameBynmId($nmId), function ($cache) use ($nmId) {
            return WbImages::find()
                ->where(['nmId' => $nmId])
                ->orderBy(['id' => SORT_ASC])
                ->all();
        }, self::getCacheTime());
    }

    private static function getCacheTime()
    {
        if (!isset(Yii::$app->params['wb-image.cacheTime'])) {
            Yii::warning("нет ключа wb-image.cacheTime");
        }
        return Yii::$app->params['wb-image.cacheTime'] ?? 0;
    }

    /**
     * @param $id
     * @return WbImages
     */
    public static function getOrSetById($id): WbImages
    {
        return \Yii::$app->cache->getOrSet(self::getCacheNameById($id), function ($cache) use ($id) {
            return WbImages::find()
                ->where(['id' => $id])
                ->one();
        }, self::getCacheTime());
    }

    /**
     * @param $id
     * @return string
     */
    private static function getCacheNameById($id): string
    {
        return "wb-image-id-$id";
    }

    public static function noImgUrl()
    {
        return "/images/no_photo.jpg";
    }

    public function getFile(){
        return Yii::getAlias("@webroot"). "/images/images/". $this->id.".jpg";
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url', 'nmId'], 'required'],
            [['upload', 'nmId', 'ya_ignore'], 'integer'],
            [['url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'Url'),
            'upload' => Yii::t('app', 'Upload'),
            'nmId' => Yii::t('app', 'Nm ID'),
            'ya_ignore' => Yii::t('app', 'Ya Ignore'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        //чистим кэш
        Yii::$app->cache->delete(self::getCacheNameBynmId($this->nmId));
        parent::afterSave($insert, $changedAttributes);
    }

    public function getUrl()
    {
        return "/get_image?id=". $this->id;
    }
}
