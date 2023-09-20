<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Controllers;

use Pha\Library\Tools;
use Services_JSON;

class KindController extends ControllerBase
{

    protected $_ext = 'jpg';

    //kindeditor的上传方法
    public function fileUploadAction()
    {
        require_once SITE_ROOT . 'lib/kindeditor/php/JSON.php';

        if (isset($_FILES['imgFile']) && !empty($_FILES['imgFile'])) {
            $upFile = $_FILES['imgFile'];
            if (!empty($upFile['error'])) {
                $this->alert('上传异常[' . $upFile['error'] . ']');
            }
            $this->checkFile($upFile);
            //保存文件
            $dir_name = !isset($this->_ap['get']['dir']) ? 'image' : trim($this->_ap['get']['dir']);
            //定义允许上传的文件扩展名
            $ext_arr = array(
                'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
                'flash' => array('swf', 'flv'),
                'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
                'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
            );
            //检查目录名
            if (!isset($ext_arr[$dir_name])) {
                $this->alert('目录名不正确');
            }
            //$this->_ext = substr(strrchr($upFile['name'], '.'), 1);
            if (!in_array(strtolower($this->_ext), ['jpeg', 'jpg', 'png', 'gif'])) {
                $this->alert('只允许上传图片文件');
            }
            $folder = date('Ymd', time());
            $fileName = date('YmdHis', time()) . Tools::getRandChar(6, Tools::NUMBERS, true);
            $path = '/uploads/' . $dir_name . '/' . $folder . '/' . $fileName . '.' . $this->_ext;
            $realPath = SITE_ROOT . 'uploads' . DS . $dir_name . DS . $folder;
            Tools::createDir($realPath);
            $realPath .= DS . $fileName . '.' . $this->_ext;
            $flag = move_uploaded_file($upFile['tmp_name'], $realPath);
            //上传结果检测及处理
            if ($flag) {
                //检测是否真文本(貌似需要fileinfo扩展)
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $fi->file($realPath);
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                    unlink($realPath);
                    $this->alert('只允许上传图片文件');
                }
                //$this->alert(['path' => $path], '上传成功');
                header('Content-type: text/html; charset=UTF-8');
                $json = new Services_JSON();
                echo $json->encode(array('error' => 0, 'url' => $path));
                exit;
            } else {
                $this->alert('文件上传失败');
            }
        }

        $this->alert('没有正常的文件可上传');
    }

    //kindeditor的文件管理列表方法
    public function fileManageAction()
    {
        require_once SITE_ROOT . 'lib/kindeditor/php/JSON.php';

        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = SITE_ROOT . 'uploads/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = '/uploads/';
        //扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png');

        $dirParam = (!isset($this->_ap['get']['dir']) || empty($this->_ap['get']['dir']))
            ? '' : $this->_ap['get']['dir'];

        //目录名
        $dir_name = trim($dirParam);
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                Tools::createDir($root_path);
            }
        }

        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }

        $current_path = str_replace('//', '/', $current_path);
        $current_url = str_replace('//', '/', $current_url);

        //排序形式，name or size or type
        global $order;
        $order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);
        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }
        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }

        //cmp_func
        usort($file_list, array($this, 'cmp_func'));

        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        $json = new Services_JSON();
        echo $json->encode($result);
        exit();
    }

    private function checkFile($upFile)
    {
        if ($upFile['error'] !== 0) {
            $this->alert('出现异常[' . $upFile['error'] . ']');
        }
        if ($upFile['type'] != 'image/jpeg' && $upFile['type'] != 'image/png' && $upFile['type'] != 'image/gif') {
            $this->alert('只允许上传图片文件');
        }
        $this->_ext = substr(strrchr($upFile['name'], '.'), 1);
        if (!in_array(strtolower($this->_ext), ['jpeg', 'jpg', 'png', 'gif'])) {
            $this->alert('只允许上传图片文件');
        }
        //200K=204800,500K=512000,1M=1048576,2M=2097152,3M=3145728,5M=5242880,10M=10485760,
        //20M=20971520,30M=31457280,40M=41943040,50M=52428800
        if ($upFile['size'] > 204800) {
            $this->alert('大小超出限制,限200K以内');
        }
    }

    //排序
    private function cmp_func($a, $b): int
    {
        global $order;
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }

    public function alert($msg)
    {
        require_once SITE_ROOT . 'lib/kindeditor/php/JSON.php';
        header('Content-type: text/html; charset=UTF-8');
        $json = new Services_JSON();
        echo $json->encode(array('error' => 1, 'message' => $msg));
        exit;
    }

}
