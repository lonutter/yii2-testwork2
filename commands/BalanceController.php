<?php
/**
 * Created by PhpStorm.
 * User: lonutter
 * Date: 27.10.2017
 */

namespace app\modules\account\commands;


use Yii;
use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\Console;
use app\modules\account\models\Users;

class BalanceController extends Controller
{
    public $defaultAction = 'info';

    /**
     * Узнать баланс пользователя по его ID
     *
     * @param $user_id
     *
     * @return int
     */
    public function actionInfo($user_id)
    {
        $user = $this->findModel($user_id);
        echo $this->ansiFormat($user->name, Console::BOLD), ': ', $this->ansiFormat($user->balance, Console::FG_GREEN);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Перевод между пользователями. Сначала указывается сумма перевода, после получатель и самым последним указываем отправителя.
     *
     *
     * @param $amount   float   Сумма перевода
     * @param $to       string  Получатель
     * @param $from     string  Отправитель
     *
     * @return int
     */
    public function actionTransfer($amount, $to, $from)
    {
        $from_user = $this->findModel($from);
        $amount = round(floatval($amount), 2, PHP_ROUND_HALF_DOWN);

        if ($amount > 0 && $from_user->balance >= $amount) {
            $to_user = $this->findModel($to);
            $to_user->balance += $amount;
            $from_user->balance -= $amount;
            $transaction = Yii::$app->db->beginTransaction();
            if ($to_user->save(false) && $from_user->save(false)) {
                $transaction->commit();
                $message = sprintf(
                    "Сумма %.2f списана с баланса %s и зачислена на баланс %s.\nНовый баланс:\n%s = %.2f;\n%s = %.2f",
                    $amount, $from_user->name, $to_user->name, $to_user->name, $to_user->balance, $from_user->name, $from_user->balance);
                echo $this->ansiFormat($message, Console::FG_GREEN);
                return Controller::EXIT_CODE_NORMAL;
            } else {
                if (!empty($to_user->errors)) {
                    $errors = $to_user->errors;
                    array_walk_recursive($errors, function($v, $k) {
                        echo $v, PHP_EOL;
                    });
                }
                if (!empty($from_user->errors)) {
                    $errors = $from_user->errors;
                    array_walk_recursive($errors, function($v, $k) {
                        echo $v, PHP_EOL;
                    });
                }
                $transaction->rollBack();
                return Controller::EXIT_CODE_ERROR;
            }
        } else {
            $error = $this->ansiFormat('Проверьте сумму перевода: она не должна быть меньше 0 и больше суммы на Вашем балансе!', Console::BOLD);
            echo $this->ansiFormat($error, Console::FG_RED);
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
    private function findModel($user_id) {
        if (($user = Users::findOne($user_id)) !== null) {
            return $user;
        } else {
            $error = $this->ansiFormat('Пользователь не найден!', Console::BOLD);
            throw new Exception($this->ansiFormat($error, Console::FG_RED));
        }
    }
}