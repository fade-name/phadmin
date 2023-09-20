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

class Config extends BaseValidate
{

    public static function chk(&$params, $gets = null): bool
    {
        if (!empty($gets)) {
            if (!Validator::isNumber($gets['edit_id'], 1, 9)) {
                return self::setErrReturn('参数错误.');
            }
        } else {
            //$params['create_time'] = $params['_cur_timestamp'];
        }

        //$v = new Validation();

        //$err = $v->validate($params);
        //if (count($err)) {
        //    return self::setErrReturn(Helpers::getMsg($err));
        //}

        //conversion

        unset($params['_cur_timestamp']);

        return true;
    }

}
