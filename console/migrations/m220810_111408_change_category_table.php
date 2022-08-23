<?php

use yii\db\Migration;

/**
 * Class m220810_111408_change_category_table
 */
class m220810_111408_change_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("category","ignore", $this->tinyInteger()->defaultValue(0)->comment('1- не выводим'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("category","ignore");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220810_111408_change_category_table cannot be reverted.\n";

        return false;
    }
    */
}
