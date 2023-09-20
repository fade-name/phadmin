
// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

/**
 * ***模块***
 * authtree参考：https://authtree.wj2015.com/
 * github：https://github.com/wangerzi/layui-authtree
 */
layui.define(['jquery', 'form', 'public', 'authtree'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let pub = layui.public;
    let authtree = layui.authtree;

    let obj = {

        //初始化列表方法
        initList: function () {
            pub.initRowDelete('/backend/authority/role/del');
            pub.checkAllAndSingle();
        },

        //初始化添加方法
        initAdd: function () {
            _this.getRuleData('add');
            _this.dataVerify();
            pub.listenToSubmit('/backend/authority/role/add', null, null, 1, '/backend/authority/role/index');
        },

        //初始化编辑方法
        initEdit: function () {
            _this.getRuleData('edit');
            _this.dataVerify();
            let editId = $('#dataForm').attr('data-edit_id');
            pub.listenToSubmit('/backend/authority/role/edit?edit_id=' + editId, 1, null, 1, '/backend/authority/role/index');
        },

        //读取权限节点
        getRuleData: function (rType) {
            let eid = '0';
            if (rType === 'edit') {
                eid = $('#dataForm').attr('data-edit_id');
            }
            $.ajax({
                type: "GET",
                url: pub.resetUrl('/backend/common/auth_rules?edit_id=' + eid),
                data: {},
                dataType: "json",
                success: function (res) {
                    if (parseInt(res.code) !== 1) {
                        console.log(res);
                        pub.tipMsg(res.msg);
                    } else {
                        authtree.render('#LAY-auth-tree-index', res.data, {
                            inputname: 'rule_str[]',
                            layfilter: 'lay-check-auth',
                            autowidth: true,
                            parentKey: 'parent_id',
                            nameKey: 'title',
                            valueKey: 'rule_path',
                            theme: 'auth-skin-default',
                            themePath: '/static/modules/tree_themes/'
                        });
                        //authtree.showAll('#LAY-auth-tree-index'); //全部展开
                    }
                },
                error: function () {
                    pub.tipMsg('请求出错!');
                }
            });
        },

        //自定义验证规则
        dataVerify: function () {
            form.verify({
                role_name: [/[\s\S]{2,30}/, '角色名称至少两个字']
            });
        },

    };

    let _this = obj;
    //输出接口
    exports('authority_role', obj);
});
