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
use Pha\Library\Seaslog;
use Pha\Library\Tools;
use Pha\Library\Validator;
use Pha\Modules\Backend\Logic\Auth;

class Role extends BaseModel
{

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段
        $this->setSource($this->_prefix . "role");
    }

    /**
     * 列表数据
     */
    public function getList($params, $pager, $pageLink = '/backend/authority/role/index?page={P}'): array
    {
        return $this->defList(
            null,
            self::class,
            'id,role_name,intro',
            $pager,
            $pageLink,
            '',
            [],
            'id',
            'id ASC'
        );
    }

    /**
     * 添加数据
     */
    public function addData($params): bool
    {
        $params['role_name'] = Tools::clearQuotes($params['role_name']);
        if (empty($params['role_name'])) {
            return $this->setErrReturn('请输入正确的角色名称');
        }
        $params['intro'] = empty($params['intro']) ? '' : Tools::clearQuotes($params['intro']);
        if (empty($params['rule_str'])) {
            return $this->setErrReturn('请至少分配一项权限');
        }
        $params['authority'] = json_encode($params['rule_str']);
        unset($params['rule_str']);
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
        $params['role_name'] = Tools::clearQuotes($params['role_name']);
        if (empty($params['role_name'])) {
            return $this->setErrReturn('请输入正确的角色名称');
        }
        $params['intro'] = empty($params['intro']) ? '' : Tools::clearQuotes($params['intro']);
        if (empty($params['rule_str'])) {
            return $this->setErrReturn('请至少分配一项权限');
        }
        $params['authority'] = json_encode($params['rule_str']);
        unset($params['rule_str']);
        $eData = self::findFirst('id=' . $gets['edit_id']);
        if (empty($eData)) {
            return $this->setErrReturn('参数不正确');
        }
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
            $datum->delete();
        }
        return true;
    }

    /**
     * 管理员角色菜单筛选
     */
    public function filtration($data): array
    {
        $conditions = Validator::isNumber(Auth::$_role_ids) ? 'id=' . Auth::$_role_ids : 'id IN (' . Auth::$_role_ids . ')';
        $roles = self::find(['conditions' => $conditions, 'columns' => 'authority'])->toArray();
        if (empty($roles)) {
            return [];
        }

        $newArr = [];
        $chkArr = [];
        foreach ($roles as $item) {
            $authority = json_decode($item['authority'], true);
            foreach ($data as $datum) {
                if (in_array($datum['rule_path'], $authority) && !in_array($datum['id'], $chkArr)) {
                    $newArr[] = $datum;
                    $chkArr[] = $datum['id'];
                }
            }
        }

        return $newArr;
    }

}
