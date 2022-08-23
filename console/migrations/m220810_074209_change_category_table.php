<?php

use yii\db\Migration;

/**
 * Class m220810_074209_change_category_table
 */
class m220810_074209_change_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("category","Ref_Key", $this->string()->comment('ref key из 1с_categories'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("category","Ref_Key");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220810_074209_change_category_table cannot be reverted.\n";

        return false;
    }
    */
}
