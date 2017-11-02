<?php

use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 */
class m171026_224848_create_users_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('users', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
            'balance' => $this->decimal(10,2)->defaultValue(0),
        ], 'ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT "Пользователи"');

        $this->batchInsert('users', ['name', 'balance'], [
            ['Александр', 1234.00],
            ['Ярослав', 4321.00],
            ['Виктор', 777.00],
            ['Григорий', 123.00],
            ['Дмитрий', 0.00],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('users');
    }
}
