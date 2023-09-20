
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
            pub.initRowDelete('/backend/general/config/del');
            pub.checkAllAndSingle();
        },

        //初始化添加方法
        initAdd: function () {
            _this.pubCtrlInit();
            _this.dataVerify();
            pub.listenToSubmit('/backend/general/config/add', null, null, 1, '/backend/general/config/index');
        },

        //初始化编辑方法
        initEdit: function () {
            _this.pubCtrlInit();
            _this.dataVerify();
            let editId = $('#dataForm').attr('data-edit_id');
            pub.listenToSubmit('/backend/general/config/edit?edit_id=' + editId, 1, null, 1, '/backend/general/config/index');
        },

        //公共控件初始化
        pubCtrlInit: function () {
            //init
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
    exports('general_config', obj);
});
