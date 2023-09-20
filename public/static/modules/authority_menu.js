
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
            pub.initRowDelete('/backend/authority/menu/del');
        },

        //初始化添加方法
        initAdd: function () {
            _this.dropDownList(0);
            _this.dataVerify();
            pub.onlyAllowNumber();
            pub.listenToSubmit('/backend/authority/menu/add', null, null, 1, '/backend/authority/menu/index');
        },

        //初始化编辑方法
        initEdit: function (sid) {
            _this.dropDownList(sid);
            _this.dataVerify();
            pub.onlyAllowNumber();
            let id = $('#dataForm').attr('data-edit_id');
            pub.listenToSubmit('/backend/authority/menu/edit?edit_id=' + id, 1, null, 1, '/backend/authority/menu/index');
        },

        //下拉框初始化
        dropDownList: function (sid) {
            let ele = $('#parent_id');
            pub.getFun({infinite: 1}, '/backend/common/get_options', function (res) {
                ele.find("option").remove();
                ele.append('<option value="0">请选择</option>');
                $.each(res.data, function (key, item) {
                    let selected = item.id == sid ? 'selected' : '';
                    let optionStr = "<option value=" + item.id + " " + selected + ">" + item.title + "</option>";
                    ele.append(optionStr);
                });
                form.render('select');
            });
        },

        //自定义验证规则
        dataVerify: function () {
            form.verify({
                rule_path: [/[a-zA-Z\/]{2,50}/, '规则路径输入不正确'],
                title: [/[\s\S]{2,40}/, '菜单名称至少两个字']
            });
        },

    };

    let _this = obj;
    //输出接口
    exports('authority_menu', obj);
});
