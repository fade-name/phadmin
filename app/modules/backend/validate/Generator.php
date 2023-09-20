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
use Pha\Library\Validator;

class Generator extends BaseValidate
{

    public static function chk(&$params): bool
    {
        if (empty($params['data_table']) || !Validator::isUserName($params['data_table'], 2, 60)) {
            return self::setErrReturn('数据表参数错误');
        }
        if (empty($params['custom_dir'])) {
            $params['custom_dir'] = '';
            $params['custom_dArr'] = [];
        } else {
            if (!Validator::isPath($params['custom_dir'])) {
                return self::setErrReturn('自定义目录名输入不正确');
            }
            $params['custom_dArr'] = explode('/', $params['custom_dir']);
            if (count($params['custom_dArr']) > 2) {
                return self::setErrReturn('自定义目录名输入最多限两级');
            }
        }
        if ($params['relation'] == 1) {
            //说明要关联数据
            if (empty($params['main_table'])) {
                return self::setErrReturn('未添加关联表');
            }
            $a2 = array_filter($params['main_table']);
            if (count($a2) != count($params['main_table'])) {
                return self::setErrReturn('请选择全部关联表');
            }
        }
        return true;
    }

}
