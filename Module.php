<?php

namespace app\modules\account;

use yii\base\Module as BaseModule;

/**
 * account module definition class
 */
class Module extends BaseModule
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\account\commands';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
