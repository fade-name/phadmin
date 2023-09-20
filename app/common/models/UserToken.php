<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Models;

use Pha\Core\BaseModel;
use Pha\Library\Tools;

class UserToken extends BaseModel
{

    public function initialize()
    {
        parent::initialize();
        $this->useDynamicUpdate(true); //设置只更新变化的字段
        $this->setSource($this->_prefix . 'user_token');
    }

    //创建TOKEN
    public function createToken($userId, $timestamp = null, $keepTime = 2592000, $transaction = null)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        //检测TOKEN，可用于限制多个TOKEN，防止多处登录
//        $res = $this->checkToken($userId, true, $timestamp, $keepTime);
//        if ($res !== false) {
//            return $res;
//        }

        $token = Tools::getRandChar(16);
        $token = md5($userId . $timestamp . $token);
        $timestamp = $timestamp + $keepTime;

        if ($transaction) {
            $this->setTransaction($transaction);
        }
        $this->assign([
            'user_id' => $userId,
            'token' => $token,
            'create_time' => time(),
            'expiration' => $timestamp
        ]);
        if (!$this->create()) {
            return false;
        }

        return [
            'token' => $token,
            'expire_time' => $timestamp
        ];
    }

    //刷新TOKEN，尽量在TOKEN快过期时刷新，或多少天刷新，如TOKEN保持30天，可在25天时刷新
    public function refreshToken($userId, $token, $usToken = null, $keepTime = 2592000): bool
    {
        if ($usToken) {
            $t = $usToken->expiration - time();
            if ($t < 1296000) {
                $usToken->expiration = (time() + $keepTime);
                $usToken->update();
            }
        } else {
            $data = self::findFirst(['conditions' => "user_id=" . $userId . " AND token='" . $token . "'"]);
            if ($data) {
                $t = $data->expiration - time();
                if ($t < 1296000) {
                    $data->expiration = (time() + $keepTime);
                    $data->update();
                }
            }
        }
        return true;
    }

    //删除TOKEN
    public function delToken($userId, $token, $usToken = null): bool
    {
        if ($usToken) {
            return $usToken->delete();
        } else {
            $data = self::findFirst(['conditions' => "user_id=" . $userId . " AND token='" . $token . "'", 'order' => 'id DESC']);
            if ($data) {
                return $data->delete();
            }
        }
        return false;
    }

    //直接检测表中是否已有用户，有则直接更新TOKEN及时间，这样目的是不用创建太多的TOKEN
    public function checkToken($userId, $needCreateNewToken = false, $timestamp = null, $keepTime = 2592000)
    {
        $data = self::findFirst(['conditions' => "user_id=" . $userId, 'order' => 'id DESC']);
        if ($data) {
            if ($needCreateNewToken) {
                $token = Tools::getRandChar(16);
                $token = md5($userId . $timestamp . $token);
                $data->create_time = $timestamp;
            } else {
                $token = $data->token;
            }
            $data->token = $token;
            $expire_time = $data->expiration;
            $t = $expire_time - $timestamp;
            if ($t < 864000) {
                $expire_time = ($timestamp + $keepTime);
                $data->expiration = $expire_time;
            }
            $data->update();
            return [
                'token' => $token,
                'expire_time' => $expire_time
            ];
        }
        return false;
    }

}
