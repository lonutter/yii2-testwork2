<?php
/**
 * Created by PhpStorm.
 * User: lonutter
 * Date: 27.10.2017
 */

namespace app\modules\account\commands;


use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\modules\account\models\Users;


class UsersController extends Controller
{
    public
        $defaultAction = 'list',
        $balance,
        $name;

    /**
     * Список ключей (опций), которые можно передать из командной строки
     *
     * @param string $actionID  текущее действие
     *
     * @return array
     */
    public function options($actionID) {
        return [
            'name',
            'balance'
        ];
    }

    /**
     * Алиасы для ключей (опций). Позволяет вместо написания полного названия ключа использовать сокращенный формат. Удобно.
     *
     * @return array
     */
    public function optionAliases() {
        return [
            'n' => 'name',
            'b' => 'balance'
        ];
    }

    /**
     * Создает нового пользователя.
     *
     * @param $name         string  Имя пользователя
     * @param int $balance  float   Начальный баланс пользователя. По умолчанию 0
     *
     * @return int
     */
    public function actionCreate($name, $balance = 0)
    {
        $user = new Users();
        $user->name = $name;
        $user->balance = $balance;

        if ($user->save()) {
            echo $this->ansiFormat(sprintf('Пользователь %s успешно создан! Его ID %d. Ему начислен баланс %.2f', $user->name, $user->id, $user->balance), Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            echo $this->ansiFormat('Ошибка при добавлении пользователя!', Console::FG_RED);
            if (!empty($user->errors)) {
                $errors = $user->errors;
                array_walk_recursive($errors, function($v, $k) {
                    echo $v, PHP_EOL;
                });
            }
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Удаление пользователя.
     *
     * @param $user_id  int Идентификатор пользователя
     *
     * @return int
     */
    public function actionDelete($user_id)
    {
        $user = $this->findModel($user_id);

        if ($user->delete()) {
            echo $this->ansiFormat("Пользователь {$user->name}(ID{$user->id}) успешно удален!", Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            echo $this->ansiFormat("Ошибка при удалении пользователя {$user->name}(ID{$user->id}). Попробуйте еще раз.", Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Если не передать ключ -n, то вернет список всех пользователей, иначе только тех, чьи имена попали под запрос.
     *
     * @return int
     */
    public function actionList()
    {
        $condition = empty($this->name) ? '' : ['like', 'name', $this->name];
        $users = Users::find()->select('*')->where($condition)->asArray()->all();

        if (empty($users)) {
            echo $this->ansiFormat('Ничего не найдено по запросу '.$this->name, Console::FG_RED);
        } else {
            printf("%-4s%-25s%-15s\n", 'ID', 'Имя', 'Баланс');

            foreach ($users as $user) {
                printf("%-4d%-25.25s%-15.2f\n", $user['id'], $user['name'], $user['balance']);
            }
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Редактирование информации о пользователе.
     * Для изменения имени нужно использовать ключ -n
     * Для изменения баланса -b
     *
     * @param $user_id  int Идентификатор пользователя
     *
     * @return int
     */
    public function actionUpdate($user_id)
    {
        $is_empty_name = empty($this->name);
        $is_empty_balance = empty($this->balance);
        if ($is_empty_name && $is_empty_balance) {
            echo $this->ansiFormat('Не передан ни один ключ. Редактировать нечего. Для удаления используйте account/users/delete USER_ID', Console::FG_RED);
            return Controller::EXIT_CODE_ERROR;
        }

        $user = $this->findModel($user_id);
        $user->name = $is_empty_name ? $user->name : $this->name;
        $user->balance = $is_empty_balance ? $user->balance : $this->balance;

        if ($user->save()) {
            echo $this->ansiFormat("Пользователь $user_id успещно отредактирован.", Console::FG_GREEN);
            return Controller::EXIT_CODE_NORMAL;
        } else {
            echo $this->ansiFormat("Ошибка при реактировании пользователя $user_id", Console::FG_RED);
            if (!empty($user->errors)) {
                $errors = $user->errors;
                array_walk_recursive($errors, function($v, $k) {
                    echo $v, PHP_EOL;
                });
            }
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Ищет пользователя по его ID
     *
     * @param $user_id
     *
     * @return \app\modules\account\commands\BalanceController|\app\modules\account\models\Users
     * @throws \yii\console\Exception
     */
    private function findModel($user_id)
    {
        if (($user = Users::findOne($user_id)) !== null) {
            return $user;
        } else {
            $error = $this->ansiFormat('Пользователь не найден!', Console::BOLD);
            throw new Exception($this->ansiFormat($error, Console::FG_RED));
        }
    }
}