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
use Pha\Modules\Backend\Logic\Auth;
use Pha\Modules\Backend\Models\Attachment;

class UploadController extends ControllerBase
{

    protected $_ext = 'jpg';

    //后台的单图上传方法（只接收图片上传）
    public function indexAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            if (isset($_FILES['file']) && !empty($_FILES['file'])) {
                $upFile = $_FILES['file'];
                $this->checkFile($upFile);
                //保存文件
                $folder = date('Ymd', time());
                $fileName = date('YmdHis', time()) . Tools::getRandChar(6, Tools::NUMBERS, true);
                $path = '/uploads/image/' . $folder . '/' . $fileName . '.' . $this->_ext;
                $realPath = SITE_ROOT . 'uploads' . DS . 'image' . DS . $folder;
                Tools::createDir($realPath);
                $realPath .= DS . $fileName . '.' . $this->_ext;
                $flag = move_uploaded_file($upFile['tmp_name'], $realPath);
                //上传结果检测及处理
                if ($flag) {
                    //检测是否真文件(貌似需要fileinfo扩展)
                    $fi = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $fi->file($realPath);
                    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                        unlink($realPath);
                        $this->error('只允许上传图片文件');
                    }
                    (new Attachment())->assign([
                        'uploader_id' => Auth::$_userId, 'up_source' => 1, 'file_path' => $path,
                        'file_name' => $fileName . '.' . $this->_ext, 'file_size' => $upFile['size'], 'create_time' => time()
                    ])->create();
                    $this->success(['path' => $path], '上传成功');
                } else {
                    $this->error('文件上传失败');
                }
            }
        }
        $this->error('请选择上传文件');
    }

    //文件上传
    public function fileAction()
    {
        if ($this->request->isPost() && $this->request->isAjax()) {
            if (isset($_FILES['file']) && !empty($_FILES['file'])) {
                $upFile = $_FILES['file'];
                $this->checkFile2($upFile);
                //保存文件
                $folder = date('Ymd', time());
                $fileName = date('YmdHis', time()) . Tools::getRandChar(6, Tools::NUMBERS, true);
                $path = '/uploads/file/' . $folder . '/' . $fileName . '.' . $this->_ext;
                $realPath = SITE_ROOT . 'uploads' . DS . 'file' . DS . $folder;
                Tools::createDir($realPath);
                $realPath .= DS . $fileName . '.' . $this->_ext;
                $flag = move_uploaded_file($upFile['tmp_name'], $realPath);
                //上传结果检测及处理
                if ($flag) {
                    (new Attachment())->assign([
                        'uploader_id' => Auth::$_userId, 'up_source' => 1, 'file_path' => $path,
                        'file_name' => $fileName . '.' . $this->_ext, 'file_size' => $upFile['size'], 'create_time' => time()
                    ])->create();
                    $this->success(['path' => $path], '上传成功');
                } else {
                    $this->error('文件上传失败');
                }
            }
        }
        $this->error('请选择上传文件');
    }

    private function checkFile($upFile)
    {
        if ($upFile['error'] !== 0) {
            $this->error('出现异常[' . $upFile['error'] . ']');
        }
        if ($upFile['type'] != 'image/jpeg' && $upFile['type'] != 'image/png' && $upFile['type'] != 'image/gif') {
            $this->error('只允许上传图片文件');
        }
        $this->_ext = substr(strrchr($upFile['name'], '.'), 1);
        if (!in_array(strtolower($this->_ext), ['jpeg', 'jpg', 'png', 'gif'])) {
            $this->error('只允许上传图片文件');
        }
        //200K=204800,500K=512000,1M=1048576,2M=2097152,3M=3145728,5M=5242880,10M=10485760,
        //20M=20971520,30M=31457280,40M=41943040,50M=52428800
        if ($upFile['size'] > 204800) {
            $this->error('图片大小超出限制，限200K以内');
        }
    }

    private function checkFile2($upFile)
    {
        if ($upFile['error'] !== 0) {
            $this->error('出现异常[' . $upFile['error'] . ']');
        }
//        if ($upFile['type'] != 'image/jpeg' && $upFile['type'] != 'image/png' && $upFile['type'] != 'image/gif') {
//            $this->error('只允许上传图片文件');
//        }
        $this->_ext = substr(strrchr($upFile['name'], '.'), 1);
        if (!in_array(strtolower($this->_ext), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'mp3', 'mp4', 'rar', 'doc', 'docx', 'txt', 'zip', 'rar', 'gz', 'bz2', 'xls', 'xlsx', 'wav', 'wmv'])) {
            $this->error('不允许上传的文件类型');
        }
        //200K=204800,500K=512000,1M=1048576,2M=2097152,3M=3145728,5M=5242880,10M=10485760,
        //20M=20971520,30M=31457280,40M=41943040,50M=52428800
        if ($upFile['size'] > 2097152) {
            $this->error('大小超出限制，限2M以内');
        }
    }

}
