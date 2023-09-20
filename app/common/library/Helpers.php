<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

use Phalcon\Http\Request;

class Helpers
{

    /**
     * 生成模板缓存文件路径的方法
     * @param string $templatePath 模板路径
     * @param mixed $config 配置
     * @param string $dir 将__DIR__传入
     * @return string
     */
    public static function mkTemplateCacheFilePath(string $templatePath, $config, string $dir): string
    {
        $basePath = $config->application->appDir;
        if ($basePath && substr($basePath, 0, 2) == '..') {
            $basePath = dirname($dir);
        }
        $basePath = realpath($basePath);
        $templatePath = trim(substr($templatePath, strlen($basePath)), '\\/');
        $filename = basename(str_replace(['\\', '/'], '_', $templatePath), '.volt') . '.php';
        $cacheDir = $config->application->cacheDir;
        if ($cacheDir && substr($cacheDir, 0, 2) == '..') {
            $cacheDir = $dir . DS . $cacheDir;
        }
        $cacheDir = realpath($cacheDir);
        if (!$cacheDir) {
            $cacheDir = sys_get_temp_dir();
        }
        if (!is_dir($cacheDir . DS . 'volt')) {
            @mkdir($cacheDir . DS . 'volt', 0755, true);
        }
        $filename = str_replace('_.._', '_', $filename);
        $filename = str_replace('_config_', '_', $filename);
        return $cacheDir . DS . 'volt' . DS . $filename;
    }

    /**
     * 得到Phalcon异常消息并返回
     * @param object $errMessage 异常消息
     * @param string $ds 连接符
     * @return mixed
     */
    public static function getMsg(object $errMessage, string $ds = '；')
    {
        $message = '';
        foreach ($errMessage as $msg) {
            $message = empty($message) ? $msg->getMessage() : $message . $ds . $msg->getMessage();
        }
        return $message;
    }

    /**
     * 分页参数
     * @param array $params 参数
     * @param integer $len 默认页大小【limit】
     * @return array
     */
    public static function pagerParams(array $params = [], int $len = 20): array
    {
        if (empty($params)) {
            $params = (new Request())->get();
        }
        $page = isset($params['page']) && (int)$params['page'] > 0 ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) && (int)$params['limit'] > 0 ? (int)$params['limit'] : $len;
        $offset = ($page - 1) * $limit;
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * 使用密码和盐值对密码进行MD5加密
     */
    public static function optimizedSaltPwd($password, $salt, $saltGain = 1)
    {
        //过滤参数
        if (!is_numeric($saltGain) || (intval($saltGain) < 0 || intval($saltGain) > 35)) {
            exit();
        }
        //对Md5盐值进行变换
        $tempSaltMd5 = md5($salt);
        for ($i = 0; $i < strlen($tempSaltMd5); $i++) {
            if (ord($tempSaltMd5[$i]) < 91 && ord($tempSaltMd5[$i]) > 32) {
                $tempSaltMd5[$i] = chr(ord($tempSaltMd5[$i]) + $saltGain);
            }
        }
        //计算哈希值
        $tempPwdMd5 = md5($password);
        return strtoupper(md5($tempSaltMd5 . $tempPwdMd5 . strrev($tempSaltMd5)));
    }

    /**
     * 无限分类数据转树型数组
     * @param array $data 无限分类数据
     * @param array $treeData 转换后的数组
     * @param integer $level 层级
     * @param string $keyField ID主键名称
     * @param string $pidField 父级键名称
     * @param int $pid 父级键值
     * @param string $childField 子数据数组键名
     */
    public static function dataToTree(array  $data, array &$treeData, int $level = 0, string $keyField = 'id',
                                      string $pidField = 'pid', int $pid = 0, string $childField = 'child')
    {
        foreach ($data as $val) {
            if ($val[$pidField] == $pid) {
                $child = [];
                $val['_level'] = $level + 1;
                self::dataToTree($data, $child, $val['_level'], $keyField, $pidField, $val[$keyField], $childField);
                if (is_array($child) && count($child)) {
                    $val[$childField] = $child;
                }
                $val['checked'] = $val['checked'] ?? false;
                $val['disabled'] = $val['disabled'] ?? false;
                $treeData[] = $val;
            }
        }
    }

    /**
     * 后台菜单专用：无限分类数据转树型数组（后台菜单专用）
     */
    public static function dataToMenuTree(array  $data, array &$treeData, string $keyField = 'id',
                                          string $pidField = 'pid', int $pid = 0, string $childField = 'child')
    {
        foreach ($data as $val) {
            if ($val[$pidField] == $pid) {
                $child = [];
                self::dataToMenuTree($data, $child, $keyField, $pidField, $val[$keyField], $childField);
                if (is_array($child) && count($child)) {
                    $val[$childField] = $child;
                    $val['rule_path'] = 'javascript:;'; //有子菜单的情况
                } else {
                    //没有子菜单的情况
                    $a = explode('/', $val['rule_path']);
                    $val['rule_path'] = count($a) > 2 ? '/backend/' . $val['rule_path'] : '/backend/' . $val['rule_path'] . '/index';
                    if (stripos($val['rule_path'], '/index') === false) {
                        $val['rule_path'] .= '/index';
                    }
                }
                if (empty($val['icon'])) {
                    $val['icon'] = empty($child) ? '&#xe6a7;' : '&#xe83c;';
                }
                $treeData[] = $val;
            }
        }
    }

    /**
     * 无限分类型数据转为树型列表数据（后台管理菜单规则列表专用）
     * @param array $data 数据数组
     * @param string $title 字段名（名称字段）
     * @param string $fieldPri 主键id
     * @param string $fieldPid 父id
     * @return array
     */
    public static function getTreeList(array  $data, string $title = 'name',
                                       string $fieldPri = 'id', string $fieldPid = 'parent_id'): array
    {
        if (empty($data)) {
            return [];
        }
        $tree = [];
        self::dataToTree($data, $tree, 0, 'id', 'parent_id');
        return self::toDateTreeList($tree);
    }

    /**
     * 树型数据转列表（后台管理菜单规则列表专用）
     */
    public static function toDateTreeList($tree, $titleField = 'title', $childKey = 'child', $childCount = 0): array
    {
        $treeList = [];
        foreach ($tree as $key => $datum) {
            $space = '';
            for ($i = 1; $i < $datum['_level']; $i++) {
                $space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            $char = $childCount <= ($key + 1) ? '└─ ' : '├─ ';
            $datum[$titleField] = empty($space) ? $datum[$titleField] : $space . $char . $datum[$titleField];
            if (empty($datum['icon'])) {
                $datum['icon'] = '&#xe83c;';
            }
            if (!empty($datum[$childKey])) {
                $data = $datum[$childKey];
                unset($datum[$childKey]);
                $treeList[] = $datum;
                $treeList = array_merge($treeList, self::toDateTreeList($data, $titleField, $childKey, count($data)));
            } else {
                $treeList[] = $datum;
            }
        }
        return $treeList;
    }

    /**
     * 递归树型数组
     * @param array $data 数据数组
     * @param string $title 字段名（显示名称字段）
     * @param string $fieldPri 主键ID字段名称
     * @param string $fieldPid 父级ID字段名称
     * @return array
     */
    public static function getTree(array  $data, string $title = 'name', string $fieldPri = 'id',
                                   string $fieldPid = 'parent_id'): array
    {
        if (empty($data)) {
            return [];
        }
        $tree = self::setLevel($data, 0, 0, $fieldPri, $fieldPid);
        foreach ($tree as &$datum) {
            $sp = '';
            for ($i = 1; $i < $datum['_level']; $i++) {
                $sp .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            $datum[$title] = $sp . '├─' . $datum[$title];
        }
        return $tree;
    }

    /**
     * 设置层级
     * @param array $data 数据数组
     * @param integer $pid 父级ID
     * @param integer $level 层级
     * @param string $fieldPri 主键ID字段名称
     * @param string $fieldPid 父级ID字段名称
     * @return array
     */
    public static function setLevel(array  $data, int $pid = 0, int $level = 0,
                                    string $fieldPri = 'id', string $fieldPid = 'parent_id'): array
    {
        $tree = [];
        if (empty($data)) {
            return [];
        }
        foreach ($data as $datum) {
            if ($datum[$fieldPid] == $pid) {
                $datum['_level'] = $level + 1;
                $tree[] = $datum;
                $tree = array_merge($tree, self::setLevel($data, $datum[$fieldPri], $datum['_level'], $fieldPri, $fieldPid));
            }
        }
        return $tree;
    }

}
