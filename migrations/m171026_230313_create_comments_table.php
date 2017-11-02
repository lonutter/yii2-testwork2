<?php

use yii\db\Migration;

/**
 * Handles the creation of table `comments`.
 * Has foreign keys to the tables:
 *
 * - `users`
 */
class m171026_230313_create_comments_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('comments', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'user_comment' => $this->text()->notNull(),
        ], 'ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT "Комментарии"');

        // creates index for column `user_id`
        $this->createIndex(
            'id1-comments-user_id',
            'comments',
            'user_id'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            '{{%fk-comments-users}}',
            'comments',
            'user_id',
            'users',
            'id',
            'CASCADE'
        );

        $this->batchInsert('comments', ['user_id', 'user_comment'], [
            [1, 'Первый комментарий от Александра.'],
            [1, 'Второй комментарий от Александра.'],
            [4, 'Привет! Я Григорий и у меня шизофрения.'],
            [1, 'Третий и последний комментарий от Александра'],
            [2, 'Первый комментарий от Ярослав.'],
            [3, 'Саня! АААААААААААААААААААААА..........'],
            [2, 'Это мой последний комментарий!'],
            [3, 'На этом все, мне больше нечего добавить! Последний комментарий'],
            [4, 'Йо-хо-хо и Бутылка Рома.'],
            [5, 'Посоны, займите сотку до з/п, а то совсем на мели.'],
            [4, 'Сейчас отправлю, Димас.'],
            [5, 'Спасибо, Гриша.'],
            [2, 'ДИМОООООН!!!'],
            [5, 'во 1) **** ты мне сделаешь вовторых ***** ***** втетьих 3)что ты мне сделаешь, я в другом городе за *** извени'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `users`
        $this->dropForeignKey(
            '{{%fk-comments-users}}',
            'comments'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'id1-comments-user_id',
            'comments'
        );

        $this->dropTable('comments');
    }
}
