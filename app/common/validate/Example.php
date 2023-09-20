<?php

namespace Pha\Validate;

use Pha\Core\BaseValidate;
use Pha\Library\Helpers;
use Phalcon\Validation;

/**
 * 例子
 */
class Example extends BaseValidate
{

    public static function checkParam($params): bool
    {
        $v = new Validation();
        $v->add('grade', new Validation\Validator\Regex([
            'message' => '请选择等级',
            'pattern' => '/^[0-9]{1,2}$/i'
        ]));
        $err = $v->validate($params);
        if (count($err)) {
            return self::setErrReturn(Helpers::getMsg($err));
        }
        return true;
    }

}
