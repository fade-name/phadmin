<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Core;

use Phalcon\Di;

class BaseValidate
{

    public static $error = '';

    protected static function setErrReturn($err = ''): bool
    {
        self::$error = $err;
        return false;
    }

    public static function getPubVerifyCode()
    {
        $session = Di::getDefault()->getShared('session');
        return $session->get('pub_verify_code', '');
    }

}
