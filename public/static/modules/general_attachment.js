
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
            pub.initRowDelete('/backend/general/attachment/del');
            pub.checkAllAndSingle();
        }

    };

    let _this = obj;
    //输出接口
    exports('general_attachment', obj);
});
