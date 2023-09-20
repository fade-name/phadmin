<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Logic;

use Pha\Core\BaseLogic;
use Pha\Library\Helpers;
use Pha\Library\Validator;
use Pha\Modules\Backend\Models\Admin;
use Pha\Modules\Backend\Models\MenuRule;
use Pha\Modules\Backend\Models\Role;
use Pha\Modules\Backend\Validate\Login;
use Phalcon\Di;
use Phalcon\Mvc\Model;

class Auth extends BaseLogic
{

    public static $_userId = null;
    public static $_isLogin = false;
    public static $_usName = '';
    public static $_role_ids = '';
    /**
     * @var Model
     */
    public static $_cur_adm = null;

    /**
     * 登录
     */
    public static function login($params): bool
    {
        if (!Login::chk($params)) {
            return self::setErrReturn(Login::$error);
        }

        $m = new Admin();
        if (!$m->login($params)) {
            return self::setErrReturn($m->error);
        }

        return true;
    }

    /**
     * 检测是否登录
     */
    public static function loginStatus()
    {
        $session = Di::getDefault()->getShared('session');
        $acc = $session->get('lgn_ses_manager', '');
        if (!empty($acc) && Validator::isUserName($acc, 5, 22)) {
            $adm = Admin::findFirst("account='" . $acc . "'");
            if ($adm && $adm->status == 'normal' && $adm->is_del == 0) {
                self::$_userId = $adm->id;
                self::$_isLogin = true;
                self::$_usName = $adm->account;
                self::$_role_ids = $adm->role_ids;
                self::$_cur_adm = $adm;
            }
        }
    }

    /**
     * 获得菜单
     */
    public static function getMenuTree(): array
    {
        if (empty(self::$_role_ids)) {
            return [];
        }
        $data = (new MenuRule())->getTree();
        if (self::$_role_ids != '*') {
            $data = (new Role())->filtration($data);
        }
        if (empty($data)) {
            return [];
        }
        $treeData = [];
        Helpers::dataToMenuTree($data, $treeData, 'id', 'parent_id');
        return $treeData;
    }

    /**
     * 管理员权限检测
     */
    public static function checkAuth($path): bool
    {
        if (self::$_role_ids == '*') {
            return true;
        }

        $conditions = Validator::isNumber(self::$_role_ids) ? 'id=' . self::$_role_ids : 'id IN (' . self::$_role_ids . ')';
        $roles = Role::find(['conditions' => $conditions, 'columns' => 'authority'])->toArray();
        if (empty($roles)) {
            return false;
        }

        $auth = false;
        foreach ($roles as $item) {
            $authority = json_decode($item['authority'], true);
            if (in_array($path, $authority)) {
                $auth = true;
                break;
            }
        }

        return $auth;
    }

}
