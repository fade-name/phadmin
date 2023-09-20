
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
layui.define(['jquery', 'form', 'public'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let pub = layui.public;

    let obj = {

        //初始化列表方法
        initList: function () {
            pub.initRowDelete('/backend/authority/admin/del');
            pub.checkAllAndSingle();
        },

        //初始化添加方法
        initAdd: function () {
            _this.customVerify();
            _this.customAddVerify();
            pub.uploadImgSet('choose_file', 'upload_now', $('#avatar'), $('#prev_img_prev'), $('#tip_txt'), '已上传');
            pub.previewImg();
            pub.listenToSubmit('/backend/authority/admin/add', null, null, 1, '/backend/authority/admin/index');
        },

        //初始化编辑方法
        initEdit: function () {
            _this.customVerify();
            _this.customEditVerify();
            pub.uploadImgSet('choose_file', 'upload_now', $('#avatar'), $('#prev_img_prev'), $('#tip_txt'), '已上传');
            pub.previewImg();
            let editId = $('#dataForm').attr('data-edit_id');
            pub.listenToSubmit('/backend/authority/admin/edit?edit_id=' + editId, 1, null, 1, '/backend/authority/admin/index');
        },

        //自定义验证规则
        customVerify: function () {
            form.verify({
                account: [/^(?!_)(?![0-9])(?!.*?_$)[a-zA-Z0-9_]{5,22}$/i, '账号只能是字母或与数字下划线的组合,5到22位.'],
                mobileCheck: function (value) {
                    if (!pub.isEmpty(value)) {
                        if (!/^1[2-9]\d{9}$/.test(value)) {
                            return '请输入正确的手机号码.';
                        }
                    }
                },
                mailCheck: function (value) {
                    if (!pub.isEmpty(value)) {
                        if (!/^[A-Za-z0-9\-_]+@[a-zA-Z0-9\-_]+(\.[a-zA-Z0-9\-_]+)+$/.test(value)) {
                            return '邮箱格式不正确.';
                        }
                    }
                }
            });
        },

        //添加时密码验证规则
        customAddVerify: function () {
            form.verify({
                pwd: [/^[\s\S]{5,22}$/, '密码长度必须是5到22位.'],
                confirmPass: function (value) {
                    if ($('#pwd').val() !== value) {
                        return '两次密码输入不一致.';
                    }
                }
            });
        },

        //编辑时自定义验证规则
        customEditVerify: function () {
            form.verify({
                pwdEdit: function (value) {
                    if (!pub.isEmpty(value)) {
                        if (!/^[\s\S]{5,22}$/.test(value)) {
                            return '密码长度必须是5到22位.';
                        }
                    }
                },
                confirmPassEdit: function (value) {
                    let c = $('#pwd');
                    if (!pub.isEmpty(c.val())) {
                        if (pub.isEmpty(value)) {
                            pub.focusAddClass($('#c_pwd'));
                            return '请输入确认密码.';
                        } else {
                            if (c.val() !== value) {
                                return '两次密码输入不一致.';
                            }
                        }
                    } else {
                        if (!pub.isEmpty(value)) {
                            pub.focusAddClass(c);
                            return '请输入密码.';
                        }
                    }
                }
            });
        },

    };

    let _this = obj;
    //输出接口
    exports('authority_admin', obj);
});
