<?php

namespace common\models;

use common\components\Helper;
use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name наименование категории
 * @property string $t транслит наименования категории
 * @property string $t_path транслит url категории
 * @property string $Ref_Key
 * @property int $count
 * @property int $parent_id
 */

class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @param int $parent_id
     * @return string
     */
    private static function getCacheNameByChilds(int $parent_id): string
    {
        return "cat-child-" . $parent_id;
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 't'], 'string', 'max' => 64],
            [['t_path', 'Ref_Key'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'наименование категории'),
            't' => Yii::t('app', 'транслит наименования категории'),
            't_path' => Yii::t('app', 'транслит url категории'),
        ];
    }

    /** Получение имени кэша по ID записи
     * @param $id
     * @return string
     */
    static public function getCacheNameById($id){
        return "category-" . $id;
    }
    /** Получение имени кэша по ID записи
     * @param $id
     * @return string
     */
    static public function getCacheNameByName($name): string
    {
        return "category-" . $name;
    }

    /**
     * @return string
     */
    static public function getCacheNameByAll(): string
    {
        return "category-all";
    }

    /** список категорий с кэшированием
     * @return mixed
     */
    public static function getParents()
    {
        return \Yii::$app->cache->getOrSet(self::getCacheNameByAll(), function ($cache) {
            return Category::find()
                ->where(['>', 'count', 0])
                ->andWhere(['ignore' => 0])
                ->andWhere(['parent_id' => 0])
                ->orderBy(['name' => SORT_ASC])
                ->all();
        }, self::getCacheTime());
    }

    /** получение имени кэша по транслит пути
     * @param $path
     * @return string
     */
    static public function getCacheNameByPath($path): string
    {
        return "category-".$path;
    }

    /** Получение данных Категории по ИД
     * @param $name
     * @return Category|null
     */
    static public function getOrSetByName($name)
    {
        if (!empty($name)) {
            return \Yii::$app->cache->getOrSet(self::getCacheNameByName($name), function ($cache) use ($name) {
                return Category::find()
                    ->where(['name' => $name])
                    ->one();
            }, self::getCacheTime());
        } else {
            return null;
        }
    }

    /**
     * @param string $Ref_Key
     * @return Category|null
     */
    public static function getOrSetByRefKey(string $Ref_Key)
    {
        if (!empty($Ref_Key)) {
            return \Yii::$app->cache->getOrSet(self::getCacheNameByName($Ref_Key), function ($cache) use ($Ref_Key) {
                return Category::find()
                    ->where(['Ref_Key' => $Ref_Key])
                    ->one();
            }, self::getCacheTime());
        } else {
            return null;
        }
    }

    /** Получение данных Категории по ИД
     * @param $id int
     * @return Category|null
     */
    static public function getOrSetById(int $id)
    {
        return \Yii::$app->cache->getOrSet(self::getCacheNameById($id), function ($cache) use($id) {
            return Category::find()
                ->where(['id' => $id])
                ->one();
        }, self::getCacheTime());
    }

    /**
     * @param $path
     * @return Category|null
     */
    static public function getOrSetByPath($path){
        if (!empty($path)) {
            return \Yii::$app->cache->getOrSet(self::getCacheNameByPath($path), function ($cache) use ($path) {
                return Category::find()
                    ->where(['t_path' => $path])
                    ->one();
            }, self::getCacheTime());
        } else {
            return null;
        }
    }

    public static function getCacheTime(){
        if (!isset(Yii::$app->params['category.cacheTime'])) {
            Yii::warning("нет ключа category.cacheTime");
        }
        return Yii::$app->params['category.cacheTime'] ?? 0;
    }

    /** генерируем slug/TPath исходя из вложенных категорий
     * @return string
     */
    public function createTPath(): string
    {
        $result = '';
        $tmps = [];
        /*$parent = $this->getParentCategory();
        if (isset($parent)) {
            $tmps [] = $parent->t;
            while (true) {
                $parent = $parent->getParentCategory();
                if (isset($parent)) {
                    $tmps[] = $parent->t;
                } else {
                    break;
                }
            }
            $tmps = array_reverse($tmps);
            $result = implode("/", $tmps);
        } else{
        */
            return $this->t . "/";
        //}

        //return $result;
    }

    /** Получение списка родительских категорий и входящих в них подкатегорий
     * @return array
     */
    public static function getParentCategories(): array
    {
        return [
            'Бортики' => ['Чехлы подушечек-бортиков'],
            'Подушки декоративные' => ["Наволочки Плюшевые на 45х45", "Наволочки Плюшевые на 50х30", "Наволочки Плюшевые Звезды"],
            'Одеяла детские' => ["Одеяла для КПБД"],
            'Пеленки' => ["Пеленки поштучно", "Нагрудники поштучно", "Салфетки поштучно"],
            'Текстиль' => ['Полотенца поштучно'],
            'Подушки для беременных' =>['Подушки U', 'Подушки L', 'Подушки R', 'Подушки С', 'Подушки T'],
            'Сумки' => ['Сумки'],
            'Разное' => [],
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert): bool
    {
        if ($insert) {
            //формируем транслиты автоматом
            $this->t = mb_strtolower(Helper::translate($this->name));
            //исправляем задвоение имен для новых записей
            $count = Category::find()
                ->where(['name' => $this->name])
                ->count();
            if ($count >= 1) {
                $this->t .= "_" . $count;
            }
            $this->t_path = $this->createTPath();
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
        //Чистим кэш если меняли данные
        Yii::$app->cache->delete(self::getCacheNameByPath($this->t_path));
        Yii::$app->cache->delete(self::getCacheNameById($this->id));
        Yii::$app->cache->delete(self::getCacheNameByName($this->name));
        Yii::$app->cache->delete(self::getCacheNameByChilds($this->id));
        parent::afterSave($insert, $changedAttributes);
    }

    /** получение родительской категории
     * @return Category|null
     */
    public function getParentCategory(){
        return self::getOrSetById($this->parent_id);
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        $items = Items1c::find()
                    ->where(['Parent_Key' => $this->Ref_Key])
                    ->all();
        $count = 0;
        /*echo $this->Ref_Key . PHP_EOL;
        echo count($items) . PHP_EOL;*/
        foreach ($items as $item){
            $wb_item = WbItems::getOrSetByBarcode($item->wb_barcode);
            $wb_item2 =WbItems::getOrSetByBarcode($item->wb_barcode_add);
            $wb_item3 =WbItems::getOrSetByBarcode($item->wb_barcode_add);
            if (isset($wb_item)) {
                $wb = $wb_item;
            } elseif(isset($wb_item2)) {
                $wb = $wb_item2;
            } elseif(isset($wb_item3)){
                $wb = $wb_item2;
            } else {
                continue;
            }

            if (isset($wb)) {
                $images = WbImages::getOrSetBynmId($wb->nmId);
                if(count($images) > 0){
                    $count++;
                }
            }

        }
        return $count;
    }

    /**
     * @return Category[]
     */
    public function getChilds(): array
    {
        $parent_id = $this->id;
        return \Yii::$app->cache->getOrSet(self::getCacheNameByChilds($parent_id), function ($cache) use ($parent_id) {
            return Category::find()
                ->where(['parent_id' => $parent_id])
                ->all();
        }, self::getCacheTime());
    }
}
