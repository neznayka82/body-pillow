<?php


namespace common\components;


use common\models\Category;
use common\models\Image;
use common\models\Import;
use common\models\Product;
use common\models\Param;
use SimpleXMLElement;
use XMLReader;
use Yii;
use yii\console\Exception;

class ImportYml
{

    public $import_file;
    private $ad_progress;
    private $categories = [];
    
    private $import;

    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /** Метод в котором происходит разбор файла XML
     * @param string $filename
     *
     * @throws \Exception
     */
    public function startParsing($filename='')
    {
        echo PHP_EOL . "Парсим файл: " . $filename . PHP_EOL;
        try{

            if(empty($filename)){
                $filename = $this->import_file;
            }
            //Проверяем что файл загрузился
            if (!file_exists($filename)) {
                $msg = "Ошибка скачивания файла. Не смогли скачать файл.";
                throw new Exception($msg);
            }
            if (filesize($filename) == 0) {
                $msg = "Файл пуст и не может быть обработан";
                throw new Exception($msg);
            }/**/

            $xml = new XMLReader();
            if (!$xml->open($filename,null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING)) {
                //$this->bad_file();
                $this->log("Не смогли открыть файл");
            }            

            //цикл чтения файла
            while($xml->read()){
                //пропускаем пока не считаем элемент целиком
                if ($xml->nodeType != \XMLReader::ELEMENT) continue;
                try {
                    //обработка элементов исходя из названия
                    switch($xml->name){
                        case 'offer': {
                            $this->ad_progress++;                            
                            //импорт объявления
                            $offer = new SimpleXMLElement($xml->readOuterXML());
                            $this->importOffer($this->ad_progress, $offer);
                            break;
                        }
                        case 'categories': {
                            //импорт категорий
                            echo "парсим категории" . PHP_EOL;
                            $categories = new SimpleXMLElement($xml->readOuterXML());
                            $this->importCategories($categories);
                            unset($categories);
                            break;
                        }                        
                        case 'currencies':{
                            //импорт валют
                            $currencies = new SimpleXMLElement($xml->readOuterXML());
                            $this->currencies = $this->xmlToArray($currencies);
                            break;
                        }                       
                    }
                } catch (\Exception $e) {
                    \Yii::$app->db->close();
                    \Yii::$app->db->open();
                    $msg = "Ошибка:" . $e->getMessage();
                    $offer_id = $this->yt_offer_id ?? "bad data";
                    $this->log($msg . " у товара с id=" . $offer_id . ". Товар не добавлен.");
                    //$this->saveErrorOffer($this->import->id, $offer_id, [$msg]);
                    echo $msg;
                }
                //есть ошибки в буфере ошибок при чтении xml
                $errors = libxml_get_errors();
                if (count($errors)){
                    $msg = "Ошибка чтения xml файла, битая структура:" . json_encode($errors);
                    throw new Exception($msg);
                }
            }
        } catch(\Exception $e){
            \Yii::$app->db->close();
            \Yii::$app->db->open();
            $msg = $e->getMessage();
            $this->log("Ошибка:" . $msg);
        }
    }

    /** Загрузка файла с удаленного ресурса $url
     * @param string $url
     * @return string|null
     */
    public function fileDownload($url = ''){
        try {
            if (empty($url)){
                $url = $this->import->url;
            }
            $filepath = Yii::getAlias('@runtime') . "/import-" . $this->import->id . ".xml";
            echo "загружаем данные в файл: " . $filepath . " источник: $url" . PHP_EOL;
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $session = curl_init($url);
            $file = fopen($filepath, 'wb');
            // defines the options for the transfer
            curl_setopt($session, CURLOPT_FILE, $file);
            curl_setopt($session, CURLOPT_HEADER, 0);
            curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            if (isset($this->import->username) && isset($this->import->password)) {
                curl_setopt($session, CURLOPT_USERPWD, $this->import->username . ":" . $this->import->password);
            }
            curl_exec($session);
            $httpcode = curl_getinfo($session, CURLINFO_HTTP_CODE);
            if ($httpcode !=200){
                $msg = "не смогли скачать файл $httpcode";
                throw new Exception($msg);
            }
            curl_close($session);
            fclose($file);
            $this->import_file = $filepath;
            echo "данные загружены успешно" . PHP_EOL;
        } catch (\Exception $e){
            $this->log($e->getMessage() . PHP_EOL . ( $url ?? ''));
            $filepath = '';
        }
        return $filepath ?? null;
    }

    /** импорт данных по товару
     * @param $ad_progress
     * @param SimpleXMLElement $offer
     */
    private function importOffer($ad_progress, SimpleXMLElement $offer)
    {
        $transaction = null;
        try{
            echo "импорт объявления №$ad_progress" . PHP_EOL;
            //начинаем транзакцию
            $transaction = Yii::$app->db->beginTransaction();

            //Импорт объявления
            $p = $this->importAd($offer);
            //Импорт параметров
            $this->importParams($p, $offer);
            //Импорт изображений
            $this->importImages($p, $offer);

            //пишем в БД
            $transaction->commit();
        } catch (\Exception $e){
            $msg = $ad_progress ." " . $e->getMessage();
            $this->log($msg);
            $transaction->rollBack();
        }
    }

    private function importCategories(SimpleXMLElement $categories)
    {
        try{
            if (count($categories) > 0) {
                //кэшируем текущие категории из БД
                $cats_tmp = Category::find()->all();
                $in_db = [];
                foreach($cats_tmp as $cat){
                    /** @var $cat Category */
                    $in_db[$cat->yml_id] = $cat;
                }
                //обходим все категории из файла
                foreach($categories as $category){
                    $attributes = $category->attributes();
                    $category_id = intval($attributes->id->__toString());
                    //echo $category_id . PHP_EOL;

                    if (!isset($in_db[$category_id])){
                        //добавляем категорию
                        $cat = new Category();
                        $cat->yml_id = $category_id;
                    } else {
                        //обновляем категорию
                        $cat = $in_db[$category_id];
                    }
                    $cat->name = trim(addslashes($category->__toString()));
                    //У нас нет вложеных категорий
                    $cat->parent_id = 0;
                    //пишем в БД
                    if( !$cat->save() ){
                        $msg = "ошибки создания категории " . $cat->name . PHP_EOL;
                        $msg .= json_encode($cat->getErrors(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        throw new Exception($msg);
                    }
                }
                $cats_tmp = Category::find()->all();
                foreach($cats_tmp as $cat){
                    /** @var $cat Category */
                    $this->categories[$cat->yml_id] = $cat;
                }
            }
        } catch (\Exception $e){
            $msg = "Ошибка разбора категорий" . $e->getMessage() ;
            $this->log($msg);
        }
    }

    private function log(string $string)
    {
        echo $string . PHP_EOL;
    }

    private function xmlToArray(SimpleXMLElement $xml)
    {
        //$xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }

    /** Импорт данных по продукту из xml
     * @param SimpleXMLElement $offer
     * @return Product|null
     * @throws Exception
     */
    private function importAd(SimpleXMLElement $offer)
    {
        $offer_id = $offer->attributes()->__toString() ?? null;
        if (!isset($offer_id)){
            $msg = "нет offer_id пропускаем";
            throw new Exception($msg);
        }
        $offer_id = intval($offer_id);
        $p = Product::getOrSetByOfferId($offer_id);
        if (!isset($p)){
            $p = new Product();
            $p->offer_id = $offer_id;
        } else {
            echo "есть в БД id=" . $p->id . PHP_EOL;
        }

        //Импорт параметров из xml в модель
        $p->name        = $this->getName($offer);
        $p->description = $this->getDescription($offer);
        $p->vendor_code = $this->getVendorCode($offer);
        $p->vendor      = $this->getVendor($offer);
        $p->price       = $this->getPrice($offer);
        $p->discount    = $this->getDiscount($offer);
        //Если есть скидка то меняем местами
        if ($p->discount !=0) {
            $tmp = $p->price;
            $p->price = $p->discount;
            $p->discount = $tmp;
        }
        $p->weight      = $this->getWeight($offer);

        //импорт размеров
        $this->getDimensions($offer,$p );
        $cat_id = intval($offer->categoryId->__toString());
        //echo "cat_id = " . $cat_id . PHP_EOL;
        $p->yml_category_id = isset($this->categories[$cat_id]) ? $this->categories[$cat_id]->id : null;
        //echo "yc = " . $p->yml_category_id . PHP_EOL;
        if (!isset($p->yml_category_id)){
            $msg = "не указана категория товара !!!";
            throw new Exception($msg);
        }

        //Пишем в БД
        if (!$p->save()){
            $msg = json_encode($p->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $msg .= json_encode($p->getAttributes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            throw new Exception($msg);
        } else {
            $this->log("Обновили/добавили товар offer_id=$offer_id" . PHP_EOL);
        }
        return $p;
    }

    /** Импорт параметров
     * @param $p Product
     * @param $offer SimpleXMLElement
     */
    private function importParams(Product $p, SimpleXMLElement $offer)
    {
        $inDbParam = Param::getByProduct($p->id);
        $inDbCache = [];
        $addedCache = [];
        foreach($inDbParam as $param){
            //создаем кэш по имени_значению т.к. имена могу повторятся
            $inDbCache[$param->name . "_" . $param->value] = $param;
        }

        foreach ($offer->param AS $param) {
            if (!isset($param['name'])) {
                echo 'Не полные параметр !isset(name, unit)' . PHP_EOL;
                continue;
            }
            foreach ($param->attributes() AS $key => $value) {
                $param_tmp[$key] = trim($value->__toString());
            }
            $param_tmp['value'] = trim($param->__toString());

            if (!isset($param_tmp['unit'])) {
                $param_tmp['unit'] = '';
            }

            $indx = $param_tmp['name']."_".$param_tmp['value'];
            //проверяем может параметр ужеесть с БД и его не нужно добавлять
            if (!isset($inDbCache[$indx])) {
                $new_param = new Param();
                $new_param->name = $param_tmp['name'];
                $new_param->product_id = $p->id;
            } else {
                $new_param =  $inDbCache[$indx];
            }
            $new_param->value = $param_tmp['value'];
            if (!empty($param_tmp['unit'])) {
                $new_param->unit = $param_tmp['unit'];
            }

            if (!$new_param->save()){
                $msg = json_encode($new_param->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                throw new Exception($msg);
            } else {
                //$inDbCache[$new_param->name . "_" . $new_param->value] = $new_param;
            }
            $addedCache[$new_param->name . "_" . $new_param->value] = $new_param;
        }
        //Проверяем что все параметры которые есть в БД были добавлены из текущего файла и нет того что нужно удалить
        foreach($inDbCache as $param){
            $indx_name = $param->name . "_" . $param->value;
            if (!isset($addedCache[$indx_name])){
                //нет параметра удаляем его
                echo "нет param $indx_name в файле импорт удалем его" . PHP_EOL;
                $param->delete();
            }
        }
    }

    /** Импорт изображений
     * @param $p Product
     * @param $offer SimpleXMLElement
     * @throws Exception
     */
    private function importImages(Product $p, SimpleXMLElement $offer)
    {
        $inDbImg = Image::getByProduct($p->id);
        $inDbImgCache = [];
        $addedImgCache = [];
        foreach($inDbImg as $image){
            /** @var $image Image **/
            $inDbImgCache[$image->url] = $image;
        }
        $i = 0;
        $mainImgId = 0;
        foreach ($offer->picture AS $img) {
            $url = trim($img);
            if (!isset($inDbImgCache[$url])){
                //нет в БД
                $img = new Image();
                $img->url = $url;
                $img->product_id = $p->id;
                $img->upload=0;
            } else {
                $img = $inDbImgCache[$url];
            }

            if ($i == 0) {
                $img->is_main = 1;
            } else {
                $img->is_main = 0;
            }

            if(!$img->save()){
                $msg = json_encode($img->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $this->log($msg);
            } else {
                $inDbImgCache[$url] = $img;
                if($i == 0) {
                    $mainImgId = $img->id;
                }
            }
            $addedImgCache[$url] = $img;
            $i++;
        }
        //удалем из БД то чего нет в файле
        foreach ($inDbImgCache as $url => $img){
            if (!isset($addedImgCache[$url])){
                echo "нет img в файле импорт удалем его" . PHP_EOL;
                $img->delete();
            }
        }
        $p->main_image_id = $mainImgId;
        if(!$p->save()){
            $msg = json_encode($p->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->log($msg);
        }
    }

    private function getName(SimpleXMLElement $offer)
    {
        $name = $offer->name->__toString() ?? '';

        if (empty($name)) {
            throw new Exception("Нет названия");
        }

        return $name;
    }

    private function getDescription(SimpleXMLElement $offer)
    {
        $description = $offer->description->__toString() ?? '';
        if (empty($description)) {
            throw new Exception("Нет описания");
        }
        return $description;
    }

    private function getVendorCode(SimpleXMLElement $offer)
    {
        $vendorCode = $offer->vendorCode->__toString() ?? '';
        if (empty($vendorCode)) {
            throw new Exception("Нет vendorCode");
        }
        return $vendorCode;
    }

    private function getVendor(SimpleXMLElement $offer)
    {
        $vendor = $offer->vendor->__toString() ?? '';
        if (empty($vendor)) {
            throw new Exception("Нет vendor");
        }
        return $vendor;
    }

    /**
     * @param SimpleXMLElement $offer
     * @return float
     * @throws Exception
     */
    private function getPrice(SimpleXMLElement $offer): float
    {
        $price = $offer->price ?? '';
        if (empty($price)) {
            throw new Exception("Нет price");
        }
        return floatval($price);
    }

    /**
     * @param SimpleXMLElement $offer
     * @return float
     * @throws Exception
     */
    private function getDiscount(SimpleXMLElement $offer): float
    {
        $discount = $offer->oldprice ?? 0;
        /*if (empty($discount)) {
            throw new Exception("Нет Discount");
        }*/
        //var_dump($offer);

        return floatval($discount);
    }

    /**
     * @param SimpleXMLElement $offer
     * @return float
     * @throws Exception
     */
    private function getWeight(SimpleXMLElement $offer): float
    {
        $weight = $offer->weight ?? '';
        if (empty($weight)) {
            throw new Exception("Нет weight");
        }
        return floatval($weight);
    }

    /**
     * @param SimpleXMLElement $offer
     * @return float
     * @throws Exception
     */
    private function getLength(SimpleXMLElement $offer): float
    {
        $length = $offer->length ?? '';
        if (empty($length)) {
            //throw new Exception("Нет length");
            //var_dump($offer);
        }
        return floatval($length);
    }

    /**
     * @param SimpleXMLElement $offer
     * @return float
     * @throws Exception
     */
    private function getWidth(SimpleXMLElement $offer): float
    {
        $width = $offer->width ?? '';
        if (empty($width)) {
            throw new Exception("Нет width");
        }
        return floatval($width);
    }

    private function getSize(SimpleXMLElement $offer)
    {
        $size = $offer->size ?? '';
        if (empty($size)) {
            throw new Exception("Нет size");
        }
        return floatval($size);
    }

    /**
     * @param SimpleXMLElement $offer
     * @param Product $p
     * @throws Exception
     */
    private function getDimensions(SimpleXMLElement $offer, Product &$p)
    {
        $dimensions = $offer->dimensions ?? '';
        if (empty($dimensions)) {
            throw new Exception("Нет dimensions");
        }

        $tmp = explode("/", $dimensions);
        if ($tmp !== false && count($tmp) > 0){
            $p->length = $tmp[0] ?? 0;
            $p->width = $tmp[1] ?? 0;
            $p->height = $tmp[2] ?? 0;
        }
    }
}