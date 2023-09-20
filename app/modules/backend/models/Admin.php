<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Models;

use Pha\Core\BaseModel;
use Pha\Library\Helpers;
use Pha\Library\Seaslog;
use Pha\Library\Tools;
use Pha\Library\Validator;
use Pha\Modules\Backend\Logic\Auth;

class Admin extends BaseModel
{

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段
        $this->setSource($this->_prefix . "admin");
    }

    /**
     * 列表数据
     */
    public function getList($params, $pager, $pageLink = '/backend/authority/admin/index?page={P}'): array
    {
        return $this->defList(
            'is_del=0',
            self::class,
            'id,account,nickname,real_name,avatar,mobile,mail,create_time,login_time,login_ip,login_count,fail_count,status',
            $pager,
            $pageLink
        );
    }

    /**
     * 管理员登录
     */
    public function login($params): bool
    {
        $data = self::findFirst("account='" . $params['username'] . "' AND is_del=0");
        if ($data) {
            $pwd = Helpers::optimizedSaltPwd($params['password'], $data->salt);
            if ($pwd != $data->password) {
                $data->assign(['fail_count' => ($data->fail_count + 1)])->update();
                return $this->setErrReturn('密码有误');
            }
            if ($data->status != 'normal') {
                return $this->setErrReturn('账号禁用');
            }
            $data->login_time = $params['_cur_timestamp'];
            $data->login_ip = Tools::getClientIp();
            $data->login_count = $data->login_count + 1;
            if (!$data->update()) {
                return $this->setErrReturn('更新异常');
            }
            return true;
        }
        return $this->setErrReturn('用户名有误');
    }

    /**
     * 添加数据
     */
    public function addData($params): bool
    {
        if (!\Pha\Modules\Backend\Validate\Admin::chk($params)) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\Admin::$error);
        }
        $cData = self::findFirst("account='" . $params['account'] . "'");
        if ($cData) {
            return $this->setErrReturn('已经存在的管理员账号');
        }
        if (!empty($params['pwd'])) {
            $salt = Tools::getRandChar(8);
            $pwd = Helpers::optimizedSaltPwd($params['pwd'], $salt);
            $params['password'] = $pwd;
            $params['salt'] = $salt;
        }
        unset($params['pwd']);
        unset($params['c_pwd']);
        if ($this->assign($params)->create()) {
            return true;
        }
        return $this->setErrReturn('抱歉，添加数据出错了');
    }

    /**
     * 编辑数据
     */
    public function editData($params, $gets): bool
    {
        if (!\Pha\Modules\Backend\Validate\Admin::chk($params, $gets)) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\Admin::$error);
        }
        $eData = self::findFirst('id=' . $gets['edit_id']);
        if (empty($eData)) {
            return $this->setErrReturn('参数不正确');
        }
        if (!empty($params['pwd'])) {
            $salt = Tools::getRandChar(8);
            $pwd = Helpers::optimizedSaltPwd($params['pwd'], $salt);
            $params['password'] = $pwd;
            $params['salt'] = $salt;
        }
        unset($params['pwd']);
        unset($params['c_pwd']);
        if ($eData->assign($params)->update()) {
            return true;
        }
        return $this->setErrReturn('抱歉，更新数据出错了');
    }

    /**
     * 删除数据
     */
    public function delData($params): bool
    {
        if (empty($params['del_id'])) {
            Seaslog::recordMsg('尝试删除（' . self::class . '）数据，但参数错误');
            return false;
        }
        if ($params['del_id'] == 1) {
            return false;
        }
        if (Validator::isNumber($params['del_id'], 1, 9)) {
            $conditions = 'id=' . $params['del_id'];
        } elseif (Validator::isIntIdsLen($params['del_id'])) {
            $conditions = 'id IN (' . $params['del_id'] . ')';
        } else {
            Seaslog::recordMsg('尝试删除（' . self::class . '）数据，但参数错误');
            return false;
        }
        $data = self::find(['conditions' => $conditions]);
        foreach ($data as $datum) {
            $datum->assign(['is_del' => 1])->update();
        }
        return true;
    }

    /**
     * 更改密码
     */
    public function changePwd($params): bool
    {
        if (empty($params['old_pwd']) || empty($params['new_pwd']) || empty($params['cfm_new_pwd'])) {
            return $this->setErrReturn('请输入正确的新旧密码');
        }
        $pwd = Helpers::optimizedSaltPwd($params['old_pwd'], Auth::$_cur_adm->salt);
        if ($pwd != Auth::$_cur_adm->password) {
            return $this->setErrReturn('旧密码不正确');
        }
        $salt = Tools::getRandChar(8);
        $pwd = Helpers::optimizedSaltPwd($params['new_pwd'], $salt);
        Auth::$_cur_adm->password = $pwd;
        Auth::$_cur_adm->salt = $salt;
        if (!Auth::$_cur_adm->update()) {
            return $this->setErrReturn('抱歉，密码修改失败');
        }
        return true;
    }

}
