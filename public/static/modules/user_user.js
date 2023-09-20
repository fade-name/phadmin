
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
layui.define(['jquery', 'form', 'laydate', 'upload', 'multiImgUpload', 'public'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let laydate = layui.laydate;
    let upload = layui.upload;
    let pub = layui.public;

    let obj = {

        //初始化列表方法
        initList: function () {
            pub.initRowDelete('/backend/user/user/del');
            pub.checkAllAndSingle();
        },

        //初始化添加方法
        initAdd: function () {
            _this.pubCtrlInit();
            _this.dataVerify();
            pub.listenToSubmit('/backend/user/user/add', null, null, 1, '/backend/user/user/index');
        },

        //初始化编辑方法
        initEdit: function () {
            _this.pubCtrlInit();
            _this.dataVerify();
            let editId = $('#dataForm').attr('data-edit_id');
            pub.listenToSubmit('/backend/user/user/edit?edit_id=' + editId, 1, null, 1, '/backend/user/user/index');
        },

        //公共控件初始化
        pubCtrlInit: function () {
            let ele_leader_id = $('#leader_id');
            pub.getFun({}, '/backend/user/user/provide_data_for_selection', function (res) {
                ele_leader_id.find("option").remove();
                ele_leader_id.append('<option value="0">请选择</option>');
                $.each(res.data, function (key, item) {
                    let selected = item.id == ck_leader_id ? ' selected' : '';
                    let optionStr = "<option value=\"" + item.id + "\"" + selected + ">" + item.user_name + "</option>";
                    ele_leader_id.append(optionStr);
                });
                form.render('select');
            });
            let ele_group_id = $('#group_id');
            pub.getFun({}, '/backend/user/user_group/provide_data_for_selection', function (res) {
                ele_group_id.find("option").remove();
                ele_group_id.append('<option value="">请选择</option>');
                $.each(res.data, function (key, item) {
                    let selected = item.id == ck_group_id ? ' selected' : '';
                    let optionStr = "<option value=\"" + item.id + "\"" + selected + ">" + item.group_name + "</option>";
                    ele_group_id.append(optionStr);
                });
                form.render('select');
            });
            let siu_avatar = upload.render({
                elem: '#siu_img_up_btn_avatar',
                url: '/backend/upload/index',
                before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#siu_prev_s_avatar').attr('src', result);
                    });
                    lIdx = layer.load(2, {time: 50 * 1000});
                },
                done: function (res) {
                    if (res.code !== 1) {
                        layer.close(lIdx);
                        $('#siu_prev_s_avatar').attr('src', '/images/img_prev.jpg');
                        $('#siu_text_tip_avatar').html(res.msg);
                    } else {
                        $('#avatar').val(res.data.path);
                        layer.close(lIdx);
                        $('#siu_text_tip_avatar').html('');
                        layer.msg('上传成功');
                    }
                }
            });
            laydate.render({elem: '#birthday'});
            laydate.render({elem: '#login_time', type: 'datetime'});
        },

        //自定义验证规则
        dataVerify: function () {
            form.verify({
                number_only: [/^\d+$/, '只能输入整数数字'],
                decimal_2f: [/^(0|([1-9]\d*))(\.\d{1,2})?$/i, '只能输入整数或两位小数'],
                decimal_nf: [/^(0|([1-9]\d*))(\.\d+)?$/i, '只能输入数字'],
            });
        },

    };

    let _this = obj;
    //输出接口
    exports('user_user', obj);
});
