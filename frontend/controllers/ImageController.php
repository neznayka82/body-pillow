<?php

namespace frontend\controllers;

use common\models\WbImages;

class ImageController extends \yii\web\Controller
{
    public function actionGet($id)
    {
        $image = WbImages::getOrSetById($id);
        if (isset($image)){
            /*if (isset($image->upload) && $image->upload == 0) {
                $image->uploadImage();
            }*/
            $path = $image->getFile();
            if (!empty($path)){
                $image_info = getimagesize($path);

                //Set the content-type header as appropriate
                header('Content-Type: ' . $image_info['mime']);
                //Set the content-length header
                header('Content-Length: ' . filesize($path));

                //Write the image bytes to the client
                readfile($path);
                die();
            }
        }
        header("HTTP/1.0 404 Not Found");
        echo "File not found.\n";
    }
}
