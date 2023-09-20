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

class MenuRule extends BaseValidate
{

    public static function chk(&$params, $gets = null): bool
    {
        if (!empty($gets)) {
            if (!Validator::isNumber($gets['edit_id'], 1, 9)) {
                return self::setErrReturn('参数错误');
            }
        } else {
            $params['create_time'] = $params['_cur_timestamp'];
        }
        $v = new Validation();
        $v->add('parent_id', new Validation\Validator\Regex([
            'message' => '上级节点参数不正确',
            'pattern' => '/^[0-9]{1,9}$/i'
        ]));
        $v->add('rule_path', new Validation\Validator\Regex([
            'message' => '规则路径不正确',
            'pattern' => '/^[a-zA-Z\/]{2,50}$/i'
        ]));
        $v->add('title', new Validation\Validator\Regex([
            'message' => '菜单名称不正确',
            'pattern' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/iu'
        ]));
        if (!empty($params['icon'])) {
            $v->add('icon', new Validation\Validator\Regex([
                'message' => '图标不正确，未确定请留空',
                'pattern' => '/^\&\#[a-z0-9]{3,7}\;$/i'
            ]));
        } else {
            $params['icon'] = '';
        }
        //url
        $v->add('weigh', new Validation\Validator\Regex([
            'message' => '排序只能是数字',
            'pattern' => '/^[0-9]{1,9}$/i'
        ]));
        $err = $v->validate($params);
        if (count($err)) {
            return self::setErrReturn(Helpers::getMsg($err));
        }
        $a = explode('/', $params['rule_path']);
        if (count($a) > 3) {
            return self::setErrReturn('规则路径最多限三级');
        }
        $params['is_menu'] = empty($params['is_menu']) ? 0 : 1;
        $params['remark'] = empty($params['remark']) ? '' : Tools::clearQuotes($params['remark']);
        if (empty($params['status']) || ($params['status'] != 'normal' && $params['status'] != 'lock')) {
            return self::setErrReturn('状态错误');
        }
        unset($params['_cur_timestamp']);

        return true;
    }

}
