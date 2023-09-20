<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers;

use Pha\Library\Helpers;
use Pha\Library\Validator;
use Pha\Models\Area;
use Pha\Modules\Backend\Models\MenuRule;
use Pha\Modules\Backend\Models\Role;
use Phalcon\Db\Enum;

/**
 * 一些公共请求
 */
class CommonController extends ControllerBase
{

    /**
     * 为下框拉提供数据（菜单节点）
     */
    public function getOptionsAction()
    {
        $this->success((new MenuRule())->getOptions($this->_ap['get']));
    }

    /**
     * 根据父ID获取子类地区（用于下拉框）
     */
    public function areaAction()
    {
        $parent_id = empty($this->_ap['get']['parent_id'])
        || !Validator::isNumber($this->_ap['get']['parent_id'], 1, 9)
            ? 0 : $this->_ap['get']['parent_id'];
        $data = Area::find([
            'conditions' => 'parent_id=' . $parent_id,
            'columns' => 'id,long_name'
        ])->toArray();
        $this->success($data);
    }

    /**
     * 获取权限节点用于角色分配（专用）
     */
    public function authRulesAction()
    {
        $params = $this->_ap['get'];
        $data = (new MenuRule())->getRules();
        if (!empty($params['edit_id']) && Validator::isNumber($params['edit_id'], 1, 9)) {
            $role = Role::findFirst(['conditions' => 'id=' . $params['edit_id'], 'columns' => 'authority']);
            if ($role) {
                $arr = json_decode($role->authority, true);
                foreach ($data as &$datum) {
                    $datum['checked'] = in_array($datum['rule_path'], $arr);
                    $datum['disabled'] = false;
                }
            }
        }
        $newData = [];
        Helpers::dataToTree($data, $newData, 0, 'id', 'parent_id', 0, 'list');
        $this->success($newData);
    }

    /**
     * 读取数据表
     */
    public function readTableAction()
    {
        $dbConfig = parent::getDI()->getDefault()->getShared('database');
        $data = $this->db->query("SELECT TABLE_NAME FROM information_schema.`TABLES` WHERE TABLE_SCHEMA='" . $dbConfig->database->dbname . "'");
        $this->success($data->fetchAll());
    }

    /**
     * 读取表字段
     */
    public function readFieldsAction()
    {
        $params = $this->_ap['get'];
        if (empty($params['tb_name']) || !Validator::isUserName($params['tb_name'], 2, 50)) {
            $this->error('参数异常');
        }
        $dbConfig = parent::getDI()->getDefault()->getShared('database');
        $sql = "SELECT COLUMN_NAME AS `value`,COLUMN_NAME AS `name` "
            . "FROM information_schema.`COLUMNS` "
            . "WHERE TABLE_SCHEMA = '" . $dbConfig->database->dbname . "' AND TABLE_NAME = '" . $params['tb_name'] . "' "
            . "ORDER BY ORDINAL_POSITION ASC";
        $data = $this->db->query($sql);
        //筛选只要字符键名的数据，不要数字下标的数据
        $data->setFetchMode(Enum::FETCH_ASSOC);
        $this->success($data->fetchAll());
    }

}
