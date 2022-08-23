<?php
namespace console\controllers;

use common\components\Helper;
use common\models\User;

class AdminController extends \yii\console\Controller
{

    public function actionAdmin($username, $password){
        /* @var $admin User*/
        $admin = User::find()
            ->where(['username' => $username])
            ->one();
        if (isset($admin)) {
            $admin->pswd = $password;
        } else {
            $admin = new User();
            $admin->username = $username;
            $admin->email = 'admin@body-pillow.ru';
            $admin->created_at = time();
            $admin->updated_at =  $admin->created_at;
        }
        $admin->setPassword($password);
        $admin->auth_key = Helper::randomPassword(10);

        $admin->save(false);
        echo 'пользователь $username создан/обновлен' . PHP_EOL;
    }

}