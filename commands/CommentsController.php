<?php
/**
 * Created by PhpStorm.
 * User: lonutter
 * Date: 27.10.2017
 */

namespace app\modules\account\commands;


use app\modules\account\models\Comments;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;

class CommentsController extends Controller
{
    public
        $user_id,
        $comment;

    public function options($actionID) {
        return [
            'user_id',
            'comment',
        ];
    }

    public function optionAliases() {
        return [
            'u' => 'user_id',
            'c' => 'comment',
        ];
    }

    /**
     * Добавляет новый комментарий $user_comment от имени пользователя $uid
     *
     * @param $uid          int     Идентификатор пользователя от имени которого нужно добавить комментарий
     * @param $user_comment string  Текст комментария
     *
     * @return int
     */
    public function actionAdd($uid, $user_comment)
    {
        $comment = new Comments();
        $comment->user_id = $uid;
        $comment->user_comment = $user_comment;

        if ($comment->save()) {
            echo $this->ansiFormat('Комментарий успешно добавлен!', Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            echo $this->ansiFormat('Ошибка при сохранении комментария!', Console::FG_RED);
            if (!empty($comment->errors)) {
                $errors = $comment->errors;
                array_walk_recursive($errors, function($v, $k) {
                    echo $v, PHP_EOL;
                });
            }
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Удаляет комментарий.
     *
     * @param $comment_id   int     Идентификатор комментария
     *
     * @return int
     */
    public function actionDelete($comment_id)
    {
        $comment = $this->findModel($comment_id);

        if ($comment->delete()) {
            echo $this->ansiFormat('Комментарий успешно удален!', Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            echo $this->ansiFormat('Ошибка при удалении комментария! Проверьте правильность передаваемых данных и попробуйте еще раз.', Console::FG_GREEN);
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Редактирует комментарий.
     *
     * @param $comment_id   int Идентификатор комментария
     *
     * @return int
     */
    public function actionUpdate($comment_id)
    {
        $is_empty_uid = empty($this->user_id);
        $is_empty_comment = empty($this->comment);

        if ($is_empty_uid && $is_empty_comment) {
            echo $this->ansiFormat('Не передано ни одного параметра. Если Вы хотите удалить комментарий, то воспользуйтесь account/comments/delete COMMENT_ID', Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $comment = $this->findModel($comment_id);
        $comment->user_id = $is_empty_uid ? $comment->user_id : $this->user_id;
        $comment->user_comment = $is_empty_comment ? $comment->user_comment : $this->comment;

        if ($comment->save()) {
            echo $this->ansiFormat('Комментарий успешно обновлен!', Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            echo $this->ansiFormat('Ошибка при сохранении комментария!', Console::FG_RED);
            if (!empty($comment->errors)) {
                $errors = $comment->errors;
                array_walk_recursive($errors, function($v, $k) {
                    echo $v, PHP_EOL;
                });
            }
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Возвращает список комментариев.
     * Можно фильтровать по пользователю (-u=USER_ID) и тексту комментария (-c="какой-нибудь текст")
     *
     * @return int
     */
    public function actionIndex()
    {
        $comments_query = Comments::find()->select('*')->with('user');

        if (!empty($this->user_id)) {
            $comments_query->where(['user_id' => $this->user_id]);
        }

        if (!empty($this->comment)) {
            $comments_query->where(['like', 'user_comment', $this->comment]);
        }

        $comments = $comments_query->all();

        if (empty($comments)) {
            echo 'Ничего не надено :-(';
        } else {
            printf("%-4s%-25s%-15s\n", 'ID', 'Имя', 'Комментарий');

            foreach ($comments as $comment) {
                printf("%-4d%-25s%-50s\n", $comment->id, $comment->user->name, $comment->user_comment);
            }
        }
        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Ищет комментарий по его ID
     *
     * @param $comment_id
     *
     * @return \app\modules\account\commands\CommentsController|\app\modules\account\models\Comments
     * @throws \yii\console\Exception
     */
    private function findModel($comment_id)
    {
        if (($comment = Comments::findOne($comment_id)) !== null) {
            return $comment;
        } else {
            $error = $this->ansiFormat('Пользователь не найден!', Console::BOLD);
            throw new Exception($this->ansiFormat($error, Console::FG_RED));
        }
    }
}