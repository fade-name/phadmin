
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
layui.define(['form', 'layer', 'public'], function (exports) {
    let $ = layui.jquery, layer = layui.layer;
    let pub = layui.public;

    let obj = {

        //上传成功时
        uploadSuccessDone: function (e, res) {
            let d = $(e).next().next().next();
            d.children().eq(0).children().last().children().eq(1).children().eq(0).attr('src', res.data.path);
            d.children().eq(0).children().last().children().eq(1).removeClass('layui-hide');
            d.children().eq(0).children().last().children().eq(0).children().eq(0).addClass('layui-hide');
            d.children().eq(0).append(
                '<div class="layui-upload-drag-self">' +
                '<div class="ico-add-btn">' +
                '<i class="iconfont">&#xe6b9;</i>' +
                '</div>' +
                '<div class="demo-prev-img layui-hide">' +
                '<img class="layui-upload-img preview-miu-show-img" src="">' +
                '<div class="handle layui-hide">' +
                '<i class="iconfont icon-myself preview-miu-btn">&#xe6e6;</i>' +
                '<i class="iconfont icon-myself del-miu-btn">&#xe69d;</i>' +
                '</div>' + '</div>' + '</div>'
            );
            _this.imagePreviewEvent();
            _this.mouseenterShowIco();
            _this.delImageBind();
            let val_e = $(e).next().next();
            _this.getUploadImgPath(val_e);
        },

        //上传点击事件绑定（点击加号）
        //要限制上传数量，则可在importModel上绑定属性设置最大数量
        uploadEventInitBind: function () {
            $('.ico-add-btn').unbind('click');
            $(document).on('click', '.ico-add-btn', function () {
                let import_horizontal = $(this).parent().parent().parent();
                let imBtn = import_horizontal.prev().prev();
                let valInput = import_horizontal.prev();
                let max = imBtn.attr('data-maxCount');
                if (max !== undefined && max !== 'undefined' && pub.isIntNumber(max)) {
                    let cc = valInput.val().split(',').length;
                    if (cc >= parseInt(max)) {
                        layer.msg('已上传最大限制' + max + '张');
                    } else {
                        imBtn.click();
                    }
                } else {
                    imBtn.click();
                }
            });
        },

        //删除图片绑定
        delImageBind: function () {
            $('.del-miu-btn').unbind('click');
            $(document).on('click', '.del-miu-btn', function () {
                let val_e = $(this).parent().parent().parent().parent().parent().prev();
                let ppt = $(this).parent().parent().parent();
                ppt.remove();
                _this.getUploadImgPath(val_e);
                return false;
            });
        },

        //图片预览绑定
        imagePreviewEvent: function () {
            $('.preview-miu-btn').unbind('click');
            $(document).on('click', '.preview-miu-btn', function () {
                let iHtml = "<img src='" + $(this).parent().parent().find('img:first').attr('src') + "' style='width: 100%; height: 100%;'/>";
                layer.open({
                    type: 1,
                    shade: false,
                    title: false, //不显示标题
                    area: ['60%', '80%'],
                    content: iHtml //捕获的元素，注意：最好该指定的元素要存放在body最外层，否则可能被其它的相对元素所影响
                });
                return false;
            });
        },

        //图片绑定鼠标悬浮显示预览和删除的图标
        mouseenterShowIco: function () {
            $('.demo-prev-img').unbind('mouseenter').unbind('mouseleave');
            //图片绑定鼠标悬浮
            $(document).on("mouseenter", ".demo-prev-img", function () {
                //鼠标悬浮
                $(this).find('div:first').removeClass('layui-hide');
            }).on("mouseleave", ".demo-prev-img", function () {
                //鼠标离开
                $(this).find('div:first').addClass('layui-hide');
            });
        },

        //每次上传或删除，重新获取图片数据
        getUploadImgPath: function (e) {
            //let imgS = $('.preview-miu-show-img');
            let imgS = e.next().children().eq(0).find('.preview-miu-show-img');
            let imgArray = [];
            imgS.each(function () {
                let url = $(this).attr('src');
                //滤空
                if (url) {
                    imgArray.push(url);
                }
            });
            e.val(imgArray.join(','));
        },

        //edi.init
        ediImageInit: function (f_name) {
            let c = $('#' + f_name);
            let v = c.val();
            if (!pub.isEmpty(v)) {
                let a = v.split(',');
                let aLen = a.length;
                for (let ai = 0; ai < a.length; ai++) {
                    c.next().children().eq(0).children().eq(ai).children().eq(1).children().eq(0).attr('src', a[ai]);
                    c.next().children().eq(0).children().eq(ai).children().eq(1).removeClass('layui-hide');
                    if (ai === 0) {
                        c.next().children().eq(0).children().eq(ai).children().eq(0).children().eq(0).addClass('layui-hide');
                    }
                    let xc = ai + 1;
                    let lh = aLen == xc ? '' : 'layui-hide';
                    let iii = c.next().children().eq(0);
                    iii.append(
                        '<div class="layui-upload-drag-self">' +
                        '<div class="ico-add-btn">' +
                        '<i class="iconfont ' + lh + '">&#xe6b9;</i>' +
                        '</div>' +
                        '<div class="demo-prev-img layui-hide">' +
                        '<img class="layui-upload-img preview-miu-show-img" src="">' +
                        '<div class="handle layui-hide">' +
                        '<i class="iconfont icon-myself preview-miu-btn">&#xe6e6;</i>' +
                        '<i class="iconfont icon-myself del-miu-btn">&#xe69d;</i>' +
                        '</div>' + '</div>' + '</div>'
                    );
                }
                _this.imagePreviewEvent();
                _this.mouseenterShowIco();
                _this.delImageBind();
            }
        }

    }

    let _this = obj;
    //输出接口
    exports('multiImgUpload', obj);
});
