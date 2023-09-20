<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Validate;

use Pha\Core\BaseValidate;
use Pha\Library\Helpers;
use Phalcon\Validation;

class Login extends BaseValidate
{

    public static function chk($params): bool
    {
        $v = new Validation();
        $v->add('username', new Validation\Validator\Regex([
            'message' => '账号输入有误',
            'pattern' => '/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]{5,22}$/i'
        ]));
        $v->add('password', new Validation\Validator\Regex([
            'message' => '请正确输入密码',
            'pattern' => '/^[\s\S]{5,22}$/iu'
        ]));
        $v->add('captcha', new Validation\Validator\Regex([
            'message' => '请正确输入验证码',
            'pattern' => '/^[a-zA-Z0-9]{4,8}$/iu'
        ]));
        $err = $v->validate($params);
        if (count($err)) {
            return self::setErrReturn(Helpers::getMsg($err));
        }

        $s = self::getPubVerifyCode();
        if ($params['captcha'] != $s) {
            return self::setErrReturn('验证码有误');
        }

        return true;
    }

}
