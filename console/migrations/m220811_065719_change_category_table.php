<?php

use yii\db\Migration;

/**
 * Class m220811_065719_change_category_table
 */
class m220811_065719_change_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("category","parent_id", $this->integer()->defaultValue(0)->comment('id родитеслькой категории'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("category","parent_id");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220811_065719_change_category_table cannot be reverted.\n";

        return false;
    }
    */
}
