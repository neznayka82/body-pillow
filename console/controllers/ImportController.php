<?php
namespace console\controllers;

use common\models\Category;
use common\models\Category1c;
use common\models\Items1c;
use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;

class ImportController extends Controller
{

    public function actionImport($id=null){
        $mutex = null;
        $mutexName = null;
        $import = null;
        try{
            if (isset($id)){
                //Получаем данные по ид
                $import = \common\models\Import::getById($id);
                $mutexName = "import-$id";
            } else {
                //Берем первый свободный импорт
                $import = \common\models\Import::find()
                    //->where(['status' => 0])
                    ->one();
                $mutexName = "import";
            }
            if (!isset($import)) {
                $msg =  "не нашли данных для импорта" ;
                throw new Exception($msg);
            }
            //блокировка множественного запуска импорт
            $mutex = new \yii\mutex\FileMutex();
            if ($mutex->acquire($mutexName) == false) {
                echo "Процесс запущен и выполняется";
                return ExitCode::OK;
            }
            $import->setIsRunning();
            $importYml = new \common\components\ImportYml($import);
            //if ( !empty($importYml->fileDownload()) ){
                $filename = Yii::getAlias('@runtime'). "/import-".$import->id.".xml";
                $importYml->startParsing($filename);
            //}

        } catch (\Exception $e){
            echo $e->getMessage() . PHP_EOL;
        } finally {
            if (isset($mutex) && isset($mutexName)) {
                $mutex->release($mutexName);
            }
            if (isset($import)) {
                $import->setIsActive();
            }
        }
        return ExitCode::OK;
    }

    public function actionTest(){
        $item = Category1c::find()
            ->where(['Ref_Key' => '38d928ef-c8c9-11e9-b1d4-e0db55ea98f7'])
            ->one();

        $c = Category::getOrSetByRefKey($item->Ref_Key);

        echo $c->getCount();
    }

    public function actionCategory(){
        try{
            $cat2parent = [];
            //кэшируем/создаем родительские категории
            foreach(Category::getParentCategories() as $name => $parent){
                $catParent = Category::find()
                    ->where(['name' => $name])
                    ->andWhere(['parent_id' => 0])
                    ->one();

                if (!isset($catParent)) {
                    //создаем родительскую категорию
                    $catParent = new Category();
                    $catParent->name = $name;
                    if(!$catParent->save()){
                        $msg = json_encode($catParent->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        $msg .= json_encode($catParent->getAttributes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        throw new Exception($msg);
                    }
                }
                $cat2parent[$name] = $catParent->id;
            }

            $items = Category1c::find()
                ->all();
            foreach($items as $item){
                /* @var $item Category1c */
                $c = Category::getOrSetByRefKey($item->Ref_Key);
                if (!isset($c)) {
                    $c = new Category();
                }
                $c->name = $item->Description;
                $c->parent_id = $cat2parent['Разное'];
                foreach(Category::getParentCategories() as $name => $children){
                    if (in_array($c->name, $children)){
                        $c->parent_id = $cat2parent[$name];
                    }
                }
                $c->count = $c->getCount();
                $c->Ref_Key = $item->Ref_Key;

                if (!$c->save()){
                    $msg = json_encode($c->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $msg .= json_encode($c->getAttributes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    throw new Exception($msg);
                }
            }

            $items = Category::find()
                ->where(['parent_id' => 0])
                ->all();
            foreach ($items as $item){
                /* @var $item Category*/
                $item->count = Category::find()
                    ->where(['parent_id' => $item->id])
                    ->sum('count');

                $item->save();
            }
        } catch (\Exception $e){
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }
    }
}