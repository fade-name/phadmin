<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Core;

use \Phalcon\Mvc\View\Engine\Volt;

class BaseVoltEngine extends Volt
{

    /**
     * 添加扩展函数
     */
    public function initFunction()
    {
        $compiler = $this->getCompiler();

        /** 添加explode函数 */
        $compiler->addFunction('explode', 'explode');

        /** 添加money_format函数 */
        $compiler->addFunction('money_format', function ($resolvedArgs, $exprArgs) use ($compiler) {
            return 'number_format(' . $resolvedArgs . ',2)';
        });

        /** 添加in_array函数 */
        $compiler->addFunction('in_array', 'in_array');

        /** 添加isset函数 */
        $compiler->addFunction('is_set', 'isset');

        /** 添加empty函数 */
        $compiler->addFunction('is_empty', 'empty');

        /** 添加date函数 */
        $compiler->addFunction('date_format', 'date');

        /** 添加stripos函数 */
        $compiler->addFunction('stripos', 'stripos');
    }

}
