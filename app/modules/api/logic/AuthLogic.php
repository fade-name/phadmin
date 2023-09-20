<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Api\Logic;

use Phalcon\Mvc\ModelInterface;
use Pha\Core\BaseLogic;
use Pha\Library\Validator;
use Pha\Models\User;
use Pha\Models\UserToken;

class AuthLogic extends BaseLogic
{

    public static $_userId = null;
    public static $_isLogin = false;
    public static $_token = '';

    /**
     * 用户名和密码登录
     * @param array $params 参数
     * @return array|bool
     */
    public static function login(array $params)
    {
        $res = \Pha\Validate\User::checkLoginParam($params);
        if (!$res) {
            return self::setErrReturn(\Pha\Validate\User::$error);
        }
        $m = new User();
        $result = $m->login($params);
        if ($result === false) {
            return self::setErrReturn($m->error);
        }
        return $result;
    }

    /**
     * 验证码登录
     * @param array $params 参数
     * @return array|bool
     */
    public static function smsLogin(array $params)
    {
        return false;
    }

    /**
     * 注册
     * @param array $params 参数
     * @return array|bool
     */
    public static function register(array $params)
    {
        $res = \Pha\Validate\User::checkRegParam($params);
        if (!$res) {
            return self::setErrReturn(\Pha\Validate\User::$error);
        }
        $m = new User();
        $result = $m->reg($params);
        if ($result === false) {
            return self::setErrReturn($m->error);
        }
        return $result;
    }

    /**
     * 验证登录状态
     * @param array $params 参数
     * @param array $header header参数
     */
    public static function verifyLogin(array $params, array $header = [])
    {
        $token = '';
        if (isset($header['Token']) && Validator::isLetterAndNumber($header['Token'])) {
            $token = $header['Token'];
        } else {
            if (isset($params['token']) && Validator::isLetterAndNumber($params['token'])) {
                $token = $params['token'];
            }
        }
        if (empty($token)) {
            self::$_isLogin = false;
        } else {
            $usToken = UserToken::findFirst(['conditions' => "token='{$token}'", 'order' => 'id DESC']);
            if ($usToken && time() < $usToken->expiration) {
                self::$_isLogin = true;
                self::$_userId = $usToken->user_id;
                (new UserToken())->refreshToken($usToken->user_id, $token, $usToken);
            } else {
                if ($usToken) {
                    (new UserToken())->delToken(self::$_userId, $token, $usToken);
                }
                self::$_isLogin = false;
            }
            self::$_token = $token;
        }
    }

    /**
     * 获取用户用于更新相关数据
     */
    public static function getUser(): ?ModelInterface
    {
        return User::findFirst(['conditions' => 'id=' . self::$_userId, 'for_updated' => true]);
    }

    /**
     * 获取用户信息
     */
    public static function getUserInfo($field = 'id,user_name,avatar,nickname,real_name,gender,mobile,money,create_time,status')
    {
        return (new User())->getUserInfo(self::$_userId, $field);
    }

    /**
     * 退出登录
     */
    public static function logout($params): bool
    {
        return self::$_isLogin && (new UserToken())->delToken(self::$_userId, $params['token']);
    }

}
