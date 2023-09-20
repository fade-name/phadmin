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
use Pha\Library\Tools;
use Pha\Library\Validator;
use Phalcon\Validation;

class User extends BaseValidate
{

    public static function chk(&$params, $gets = null): bool
    {
        if (!empty($gets)) {
            if (!Validator::isNumber($gets['edit_id'], 1, 9)) {
                return self::setErrReturn('参数错误.');
            }
            if ($params['leader_id'] == $gets['edit_id']) {
                return self::setErrReturn('推广人不能选择本人.');
            }
        } else {
            $params['create_time'] = $params['_cur_timestamp'];
        }

        if (empty($gets)) {
            $v = new Validation();
            $v->add('user_name', new Validation\Validator\Regex([
                'message' => '用户名最少5位字母或与数字的组合',
                'pattern' => '/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]{5,22}$/i'
            ]));
            $err = $v->validate($params);
            if (count($err)) {
                return self::setErrReturn(Helpers::getMsg($err));
            }
        }

        if (!empty($params['password'])) {
            $salt = Tools::getRandChar(8);
            $params['password'] = Helpers::optimizedSaltPwd($params['password'], $salt);
            $params['salt'] = $salt;
        }

        unset($params['_cur_timestamp']);
        $params = Tools::clearAllQuotes($params);

        return true;
    }

}
