<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Api\Controllers;

use Pha\Core\BaseController;
use Pha\Library\UrlSign;
use Pha\Library\Validator;
use Pha\Modules\Api\Logic\AuthLogic;
use Phalcon\Config\Adapter\Php;

class ControllerBase extends BaseController
{

    public $_setting; //相关配置

    public function initialize()
    {
        parent::initialize();
        //得到相关配置
        $this->getSetting();
        //验证URL签名及参数
        $this->verifyURLSignParams();
        //验证是否登录
        $this->verifyLogin();
    }

    //得到相关配置
    public function getSetting()
    {
        $this->_setting = new Php(APP_PATH . '/config/setting.php');
        $this->di->setShared('settings', $this->_setting);
    }

    //验证URL签名及参数
    public function verifyURLSignParams()
    {
        $this->checkUrlSignBefore();
        //过滤_url
        $param = [];
        foreach ($this->_ap['get'] as $key => $value) {
            if ($key != '_url') {
                $param[$key] = $value;
            }
        }
        $result = UrlSign::generateSign($param, $this->_setting['secret']);
        if ($result['sign'] != $this->_ap['get']['sign_str']) {
            $this->error('签名错误', $result);
        }
        if (!Validator::isNumber($this->_ap['get']['timestamp_str'], 9, 13)) {
            $this->error('时间戮参数错误');
        }
        $f = floatval($this->_ap['get']['timestamp_str']) + $this->_setting['req_timeout'];
        if ($f < $this->_cur_timestamp) {
            $this->error('请求超时');
        }
    }

    //检测必需的URL签名验证参数
    public function checkUrlSignBefore()
    {
        (!isset($this->_ap['get']['nonce_str']) || empty($this->_ap['get']['nonce_str'])) && $this->error('参数错误');
        (!isset($this->_ap['get']['timestamp_str']) || empty($this->_ap['get']['timestamp_str'])) && $this->error('参数不正确');
        (!isset($this->_ap['get']['sign_str']) || empty($this->_ap['get']['sign_str'])) && $this->error('参数异常');
    }

    //验证登录
    public function verifyLogin()
    {
        AuthLogic::verifyLogin($this->_ap['get'], $this->_ap['header']);
        //需要排除不需要验证登录的方法
        //$path = $this->dispatcher->getControllerName() . '_' . $this->dispatcher->getActionName();
        //...
        //是否登录
        if (!AuthLogic::$_isLogin) {
            $this->error('请先登录', [], 401);
        }
    }

}
