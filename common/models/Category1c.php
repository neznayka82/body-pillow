<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%1c_categories}}".
 *
 * @property string $Ref_Key
 * @property string $Parent_Key
 * @property string $Code
 * @property string $Description
 */
class Category1c extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%1c_categories}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Ref_Key', 'Parent_Key', 'Code', 'Description'], 'required'],
            [['Ref_Key', 'Parent_Key', 'Code', 'Description'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Ref_Key' => Yii::t('app', 'Ref Key'),
            'Parent_Key' => Yii::t('app', 'Parent Key'),
            'Code' => Yii::t('app', 'Code'),
            'Description' => Yii::t('app', 'Description'),
        ];
    }
}
