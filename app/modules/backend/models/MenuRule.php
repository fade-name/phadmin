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
use Pha\Library\Validator;

class MenuRule extends BaseModel
{

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段
        $this->setSource($this->_prefix . "menu_rule");
    }

    /**
     * 列表数据
     */
    public function getList($params, $pager, $pageLink = '/backend/authority/menu/index?page={P}'): array
    {
        $data = $this->defList(
            'is_menu=1 AND is_del=0',
            self::class,
            'id,parent_id,rule_path,title,icon,url,is_menu,create_time,weigh,status',
            $pager,
            $pageLink,
            '',
            [],
            'id',
            'weigh ASC,id ASC'
        );
        return Helpers::getTreeList($data['list'], false, 'title');
    }

    /**
     * 添加数据
     */
    public function addData($params): bool
    {
        $res = \Pha\Modules\Backend\Validate\MenuRule::chk($params);
        if (!$res) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\MenuRule::$error);
        }
        if (!empty($params['parent_id'])) {
            $cData = self::findFirst(['conditions' => 'id=' . $params['parent_id'], 'columns' => 'id,is_del']);
            if (empty($cData)) {
                return $this->setErrReturn('上级节点参数不正确');
            }
            if ($cData->is_del == 1) {
                return $this->setErrReturn('上级节点已删除');
            }
        }
        $cData2 = self::findFirst(['conditions' => "rule_path='" . $params['rule_path'] . "'", 'columns' => 'id']);
        if ($cData2) {
            return $this->setErrReturn('规则路径已存在');
        }
        if ($this->assign($params)->create()) {
            if (!empty($params['parent_id'])) {
                $this->getModelsManager()->executeQuery('UPDATE ' . self::class . ' SET subordinate=1 WHERE id=' . $params['parent_id']);
            }
            return true;
        }
        return $this->setErrReturn('抱歉，添加数据出错了');
    }

    /**
     * 编辑数据
     */
    public function editData($params, $gets): bool
    {
        $res = \Pha\Modules\Backend\Validate\MenuRule::chk($params, $gets);
        if (!$res) {
            return $this->setErrReturn(\Pha\Modules\Backend\Validate\MenuRule::$error);
        }
        $eData = self::findFirst('id=' . $gets['edit_id']);
        if (empty($eData)) {
            return $this->setErrReturn('参数不正确');
        }
        if (!empty($params['parent_id'])) {
            $cData = self::findFirst(['conditions' => 'id=' . $params['parent_id'], 'columns' => 'id,is_del']);
            if (empty($cData)) {
                return $this->setErrReturn('上级节点参数不正确');
            }
            if ($cData->is_del == 1) {
                return $this->setErrReturn('上级节点已删除');
            }
        }
        if ($params['rule_path'] != $eData->rule_path) {
            $cData2 = self::findFirst(['conditions' => "rule_path='" . $params['rule_path'] . "'", 'columns' => 'id']);
            if ($cData2) {
                return $this->setErrReturn('规则路径已存在');
            }
        }
        //是否变更了父级，若是，需判断父级是否还有子类，新选择的父类也要变更subordinate字段
        $fatherData = null;
        if ($eData->parent_id != $params['parent_id']) {
            $fatherData = self::findFirst('id=' . $eData->parent_id);
        }
        if ($eData->assign($params)->update()) {
            if ($fatherData) {
                //判断原父级是否还有子类
                $sData = self::findFirst('parent_id=' . $fatherData->id . ' AND is_del=0');
                if (empty($sData)) {
                    //更改subordinate字段
                    $fatherData->assign(['subordinate' => 0])->update();
                }
                //新选择的父类也要变更subordinate字段
                $this->getModelsManager()->executeQuery('UPDATE ' . self::class . ' SET subordinate=1 WHERE id=' . $params['parent_id']);
            }
            return true;
        }
        return $this->setErrReturn('抱歉，更新数据出错了');
    }

    /**
     * 获取数据用于管理的左侧菜单展示
     */
    public function getTree(): array
    {
        $data = $this->defList(
            "is_menu=1 AND status='normal' AND is_del=0",
            self::class,
            'id,parent_id,rule_path,title,icon,url',
            [],
            '',
            '',
            [],
            'id',
            'weigh ASC,id ASC'
        );
        return $data['list'];
    }

    /**
     * 获取各节点用于权限筛选
     */
    public function getRules()
    {
        $data = $this->defList(
            "status='normal' AND is_del=0",
            self::class,
            'id,parent_id,rule_path,title',
            [],
            '',
            '',
            [],
            'id',
            'weigh ASC,id ASC'
        );
        return $data['list'];
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
            $upData = ['subordinate' => $datum->subordinate, 'is_del' => 1];
            $sData = self::findFirst('parent_id=' . $datum->id . ' AND is_del=0');
            if (empty($sData)) {
                $upData['subordinate'] = 0;
            }
            $datum->assign($upData)->update();
        }
        return true;
    }

    /**
     * 为下拉框提供数据
     */
    public function getOptions($params): array
    {
        $date = self::find([
            'conditions' => 'is_menu=1 AND is_del=0',
            'columns' => 'id,parent_id,title',
            'order' => 'weigh ASC,id ASC'
        ])->toArray();
        if (!empty($params['infinite'])) {
            $date = Helpers::getTreeList($date, 'title');
        }
        return $date;
    }

}
