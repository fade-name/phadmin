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

class Admin extends BaseValidate
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
        $params['nickname'] = Tools::clearQuotes($params['nickname']);
        $params['real_name'] = Tools::clearQuotes($params['real_name']);
        $params['avatar'] = Tools::clearQuotes($params['avatar']);
        if (empty($params['avatar'])) {
            $params['avatar'] = '/images/us_avatar.png';
        }
        $v = new Validation();
        if (empty($gets)) {
            $v->add('account', new Validation\Validator\Regex([
                'message' => '账号最少5位字母或与数字的组合',
                'pattern' => '/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]{5,22}$/i'
            ]));
        } else {
            unset($params['account']);
        }
        if (empty($gets)) {
            $v->add('pwd', new Validation\Validator\Regex([
                'message' => '密码长度必须是5到22位.',
                'pattern' => '/^[\s\S]{5,22}$/iu'
            ]));
            $v->add('c_pwd', new Validation\Validator\Confirmation([
                'message' => '两次密码输入的不一致.',
                'with' => 'pwd'
            ]));
        } else {
            if (isset($params['pwd']) && !empty($params['pwd'])) {
                $v->add('pwd', new Validation\Validator\Regex([
                    'message' => '密码长度必须是5到22位.',
                    'pattern' => '/^[\s\S]{5,22}$/iu'
                ]));
                $v->add('c_pwd', new Validation\Validator\Confirmation([
                    'message' => '两次密码输入的不一致.',
                    'with' => 'pwd'
                ]));
            }
        }
        if (!empty($params['mobile'])) {
            $v->add('mobile', new Validation\Validator\Regex([
                'message' => '请输入正确的手机号码',
                'pattern' => '/^1[2-9]\d{9}/i'
            ]));
        }
        if (!empty($params['mail'])) {
            $v->add('mail', new Validation\Validator\Email([
                'message' => '邮箱格式不正确.'
            ]));
        }
        $err = $v->validate($params);
        if (count($err)) {
            return self::setErrReturn(Helpers::getMsg($err));
        }
        //status
        if (empty($params['status']) || ($params['status'] != 'normal' && $params['status'] != 'lock')) {
            return self::setErrReturn('状态错误');
        }
        //角色
        if (empty($params['role_ids'])) {
            return self::setErrReturn('请分配角色');
        } else {
            $params['role_ids'] = implode(',', $params['role_ids']);
            if (!Validator::isIntIdsLen($params['role_ids'])) {
                return self::setErrReturn('请分配角色');
            }
        }
        unset($params['_cur_timestamp']);

        return true;
    }

}
