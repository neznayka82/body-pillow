<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "import".
 *
 * @property int $id
 * @property string|null $url url откуда берутся данные
 * @property int|null $user_id id пользоватебя добавившего запись
 * @property int|null $last_start когда запускали  unix time
 * @property int|null $status статус импорта
 * @property string $password
 * @property string $username
 */
class Import extends \yii\db\ActiveRecord
{
    const STATUS_IS_ACTIVE = 0;
    const STATUS_IS_RUNNING = 1;
    const STATUS_IS_OFF = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'import';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'last_start', 'status'], 'integer'],
            [['url'], 'string', 'max' => 512],
            [['username', 'password'], 'string', 'max' => '256'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'url' => Yii::t('app', 'url откуда берутся данные'),
            'user_id' => Yii::t('app', 'id пользоватебя добавившего запись'),
            'las_start' => Yii::t('app', 'когда запускали  unix time'),
            'status' => Yii::t('app', 'статус импорта'),
        ];
    }

    /** Получаем импорт по ид
     * @param $id
     * @return Import|null|ActiveRecord
     */
    public static function getById($id){
        return Import::find()
            ->where(['id' => $id])
            ->one();
    }

    /**
     * Импорт сейчас выполняется
     */
    public function setIsRunning()
    {
        $this->status = self::STATUS_IS_RUNNING;
        $this->last_start = time();
        if (!$this->save()){
            $msg = json_encode($this->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->log($msg);
        }
    }

    /**
     * Импорт может быть выполнен
     */
    public function setIsActive()
    {
        $this->status = self::STATUS_IS_ACTIVE;
        if (!$this->save()){
            $msg = json_encode($this->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->log($msg);
        }
    }


}
