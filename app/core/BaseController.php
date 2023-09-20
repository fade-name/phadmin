<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Core;

use Phalcon\Mvc\Controller;
use Phalcon\Session\Adapter\Stream as SessionAdapter;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Session\ManagerInterface;
use Redis;

class BaseController extends Controller
{

    /**
     * 当前时间戮
     */
    public $_cur_timestamp;
    /**
     * @var ManagerInterface
     */
    public $_session;
    /**
     * @var Redis
     */
    public $_redis;
    /**
     * 参数集
     */
    public $_ap = [];

    public function initialize()
    {
        if ($this->request->isOptions()) {
            $this->success();
        }
        $this->_cur_timestamp = time();
        $this->_redis = parent::getDI()->getShared('redis');
        $this->getAllParams();
    }

    /**
     * 获取参数集
     */
    private function getAllParams()
    {
        //$this->_ap['all'] = $this->request->get();
        $this->_ap['get'] = $this->request->getQuery();
        //$this->_ap['get']['_cur_timestamp'] = $this->_cur_timestamp;
        $this->_ap['post'] = $this->request->getPost();
        $this->_ap['post']['_cur_timestamp'] = $this->_cur_timestamp;
        $this->_ap['header'] = $this->request->getHeaders();
        $this->_ap['input'] = file_get_contents('php://input');
    }

    /**
     * 初始化session
     */
    public function initSession()
    {
        $this->getDI()->setShared('session', function () {
            $session = new SessionManager();
            $files = new SessionAdapter([
                'savePath' => sys_get_temp_dir(),
            ]);
            $session->setAdapter($files);
            $session->start();
            return $session;
        });
    }

    #region 输出JSON至前端

    /**
     * 成功时反回
     * @param array $data 数据
     * @param string $msg 消息
     */
    protected function success(array $data = array(), string $msg = 'SUCCESS', $statusCode = 200)
    {
        $data = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
            'statusCode' => $statusCode
        ];
        echo json_encode($data);
        exit();
    }

    /**
     * 失败时返回
     * @param string $msg 消息
     * @param array $data 数据
     */
    protected function error(string $msg = 'FAIL', array $data = array(), $statusCode = 200)
    {
        $data = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
            'statusCode' => $statusCode
        ];
        echo json_encode($data);
        exit();
    }

    #endregion

}
