
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
    let pub = layui.public;

    let obj = {

        //初始化列表方法
        initList: function () {
            $('.listForOptimize').click(function () {
                let _t = $(this);
                let tn = _t.attr('data-tn');
                layer.confirm('确定要操作吗?', {btn: ['确定', '取消'], title: "提示"}, function (index) {
                    layer.close(index);
                    pub.postFun({'table_name': tn}, '/backend/general/database/optimize', null, 1, function (res) {
                        //延迟
                        layer.msg(res.msg, {icon: 1, time: 1000, shade: 0.4}, function () {
                            location.reload();
                        });
                    });
                }, function (index) {
                    layer.close(index);
                });
            });
            $('.listForRepair').click(function () {
                let _t = $(this);
                let tn = _t.attr('data-tn');
                layer.confirm('确定要操作吗?', {btn: ['确定', '取消'], title: "提示"}, function (index) {
                    layer.close(index);
                    pub.postFun({'table_name': tn}, '/backend/general/database/repair', null, 1, function (res) {
                        //延迟
                        layer.msg(res.msg, {icon: 1, time: 1000, shade: 0.4}, function () {
                            location.reload();
                        });
                    });
                }, function (index) {
                    layer.close(index);
                });
            });
        }

    };

    let _this = obj;
    //输出接口
    exports('general_database', obj);
});
