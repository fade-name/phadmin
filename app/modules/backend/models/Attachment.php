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
use Pha\Library\Validator;

class Attachment extends BaseModel
{

    protected $_infinite_level = false;

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段
        $this->setSource($this->_prefix . "attachment");
    }

    /**
     * 列表数据
     */
    public function getList($params, $pager, $pageLink = '/backend/general/attachment/index?page={P}'): array
    {
        $data = $this->defList(
            null,
            self::class,
            'id,up_source,file_path,file_name,file_size,create_time',
            $pager,
            $pageLink
        );
        foreach ($data['list'] as $key => &$datum) {
            $datum['create_time'] = date('Y-m-d H:i:s', $datum['create_time']);
        }
        return $data;
    }

    /**
     * 删除数据
     */
    public function delData($params): bool
    {
        if (empty($params['del_id'])) {
            return false;
        }
        if (Validator::isNumber($params['del_id'], 1, 9)) {
            $conditions = 'id=' . $params['del_id'];
        } elseif (Validator::isIntIdsLen($params['del_id'])) {
            $conditions = 'id IN (' . $params['del_id'] . ')';
        } else {
            return false;
        }
        $data = self::find(['conditions' => $conditions]);
        foreach ($data as $datum) {
            $file = rtrim(SITE_ROOT, DS) . $datum->file_path;
            unlink($file);
            $datum->delete();
        }
        return true;
    }

}
