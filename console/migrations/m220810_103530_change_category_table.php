<?php

use yii\db\Migration;

/**
 * Class m220810_103530_change_category_table
 */
class m220810_103530_change_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("category","count", $this->integer()->defaultValue(0)->comment('число продуктов из 1с_items для этой категории'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("category","count");
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220810_103530_change_category_table cannot be reverted.\n";

        return false;
    }
    */
}
