
// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

/**
 * ***模块***
 */
layui.define(['jquery', 'form', 'upload', 'public'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let upload = layui.upload;
    let pub = layui.public;

    let obj = {

        //初始化
        initEdit: function () {
            _this.pubCtrlInit();
            pub.listenToSubmit('/backend/general/profile/index', null, null, 1, '/backend/general/profile/index');
        },

        //公共控件初始化
        pubCtrlInit: function () {
            upload.render({
                elem: '#avatar_btn',
                url: '/backend/upload/index',
                before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#avatar_prev').attr('src', result);
                    });
                },
                done: function (res) {
                    if (res.code !== 1) {
                        $('#avatar_prev').attr('src', '/images/img_prev.jpg');
                        $('#avatar_tip').html(res.msg);
                    } else {
                        $('#avatar').val(res.data.path);
                        $('#avatar_tip').html('');
                        layer.msg('上传成功');
                    }
                }
            });
        },

        //修改密码
        initChangePwd: function () {
            _this.customPwdVerify();
            _this.listenToPwdSubmit();
        },

        //修改时密码验证规则
        customPwdVerify: function () {
            form.verify({
                pass: [/^(.+){5,22}$/, '密码长度必须是5到22位'],
                repass: function (value) {
                    if ($('#new_pwd').val() !== $('#cfm_new_pwd').val()) {
                        return '两次新密码不一致';
                    }
                }
            });
        },

        //修改密码提交
        listenToPwdSubmit: function () {
            pub.listenToSubmit('/backend/general/profile/password', null, null, 1, '/backend/general/profile/password');
        },

    };

    let _this = obj;
    //输出接口
    exports('general_profile', obj);
});
