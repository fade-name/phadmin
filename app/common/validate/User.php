<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Validate;

use Pha\Core\BaseValidate;
use Pha\Library\Helpers;
use Phalcon\Validation;

class User extends BaseValidate
{

    public static function checkLoginParam(&$params, $isEdit = false): bool
    {
        $v = new Validation();
        $v->add('user_name', new Validation\Validator\Regex([
            'message' => '用户名不正确',
            'pattern' => '/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]{5,22}$/i'
        ]));
        $v->add('password', new Validation\Validator\Regex([
            'message' => '密码不正确',
            'pattern' => '/^[\s\S]{5,22}$/iu'
        ]));
        $err = $v->validate($params);
        if (count($err)) {
            return self::setErrReturn(Helpers::getMsg($err));
        }
        return true;
    }

    public static function checkRegParam(&$params, $isEdit = false): bool
    {
        $v = new Validation();
        $v->add('user_name', new Validation\Validator\Regex([
            'message' => '用户名只能是字母或与数字的组合5到22位',
            'pattern' => '/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]{5,22}$/i'
        ]));
        $v->add('password', new Validation\Validator\Regex([
            'message' => '密码由5-22个字符组成，区分大小写',
            'pattern' => '/^[\s\S]{5,22}$/iu'
        ]));
        $v->add('pwd_confirm', new Validation\Validator\Confirmation([
            'message' => '两次密码输入的不一致',
            'with' => 'password'
        ]));
        $err = $v->validate($params);
        if (count($err)) {
            return self::setErrReturn(Helpers::getMsg($err));
        }
        return true;
    }

}
