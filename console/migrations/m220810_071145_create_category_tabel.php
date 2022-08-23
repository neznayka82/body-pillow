<?php

use yii\db\Migration;

/**
 * Class m220810_071145_create_category_tabel
 */
class m220810_071145_create_category_tabel extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            't' => $this->string()->notNull()->unique(),
            't_path' => $this->string()->notNull()->unique()
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropTable("category");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220810_071145_create_category_tabel cannot be reverted.\n";

        return false;
    }
    */
}
