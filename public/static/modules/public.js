
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
layui.define(['jquery', 'form', 'upload'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let upload = layui.upload;

    let obj = {

        //是否为空
        isEmpty: function (str) {
            let s = String(str); //强制转换
            return s === ""
                || s.replace(/^\s*|\s*$/g, "") === ""
                || s.replace(/　/g, "") === "";
        },

        //时间戮转日期(2020-05-20 05:20:00)
        timestampToDate: function (ts) {
            let date = new Date(ts * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
            let Y = date.getFullYear();
            let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
            let D = date.getDate();
            let h = date.getHours();
            let m = date.getMinutes();
            let s = date.getSeconds();
            //return Y+M+D+h+m+s;
            return Y + '-' + M + '-' + D + ' ' + h + ':' + m + ':' + s;
        },

        //获取年月日(20200520)
        getDateString: function () {
            let now = new Date();
            let year = now.getFullYear(); //得到年份
            let month = now.getMonth(); //得到月份
            let date = now.getDate(); //得到日期
            month = month + 1;
            if (month < 10) {
                month = '0' + month;
            }
            if (date < 10) {
                date = '0' + date;
            }
            return year.toString() + month.toString() + date.toString();
        },

        //日期串加随机数(20200520052000123456)
        datetimeString: function () {
            let now = new Date();
            let year = now.getFullYear(); //得到年份
            let month = now.getMonth(); //得到月份
            let date = now.getDate(); //得到日期
            let hour = now.getHours(); //得到小时
            let minutes = now.getMinutes(); //得到分钟
            let sec = now.getSeconds(); //得到秒
            month = month + 1;
            if (month < 10) {
                month = '0' + month;
            }
            if (date < 10) {
                date = '0' + date;
            }
            if (hour < 10) {
                hour = '0' + hour;
            }
            if (minutes < 10) {
                minutes = '0' + minutes;
            }
            if (sec < 10) {
                sec = '0' + sec;
            }
            let n = _this.randomRange(100000, 999999);
            return year.toString() + month.toString() + date.toString() + hour.toString() + minutes.toString() + sec.toString() + n.toString();
        },

        //随机数
        randomRange: function (min, max) {
            return Math.floor(Math.random() * (max - min)) + min;
        },

        //替换全部
        replaceAllTo: function (findStr, repStr, objStr) {
            let reg = new RegExp(findStr, "gm");
            return objStr.replace(reg, repStr);
        },

        //是否数字（1到9位的整数）
        isIntNumber: function (str) {
            let s = String(str); //强制转换
            return s.search(/^\d{1,9}$/) !== -1;
        },

        //是否数字串方法1，不限数字长度（12,323463453634367574,56）
        isIntIdsLen1: function (str) {
            let s = String(str); //强制转换
            return s.search(/^(?:\d\,)*\d$/) !== -1;
        },

        //是否数字串方法2，数字限9位以内（12,323474,56）
        isIntIdsLen2: function (str) {
            let s = String(str); //强制转换
            return s.search(/^(?:\d{1,9}\,)*\d{1,9}$/) !== -1;
        },

        //是否时间格式，如：20:30:50
        isTime: function (str) {
            let s = String(str); //强制转换
            s = '2020-05-20 ' + s;
            return s.search(/^(((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-)) (20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/) !== -1;
        },

        //清除单引号
        clearQuotes: function (s) {
            return s.replace(/'/g, "");
        },

        //刷新验证码
        captchaRefresh: function (e) {
            let ts = new Date().getTime();
            e.attr('src', '/captcha?t=' + ts);
        },

        //指定元素获得焦点及添加样式
        focusAddClass: function (e) {
            e.focus();
            e.addClass('layui-form-danger');
        },

        //提示方法
        tipMsg: function (msg) {
            layer.msg('<span style="color:#FFFFFF;">' + msg + '</span>');
        },

        //列表的行删除方法
        initRowDelete: function (url) {
            //单行删除
            $('.listForDel').click(function () {
                let _t = $(this);
                let id = _t.attr('data-id');
                layer.confirm('确定要删除行吗?', {btn: ['确定', '取消'], title: "提示"}, function (index) {
                    layer.close(index);
                    _this.postFun({'del_id': id}, url, null, 1, function (res) {
                        //延迟
                        layer.msg(res.msg, {icon: 1, time: 1000, shade: 0.4}, function () {
                            _t.parent('td').parent('tr').remove();
                        });
                    });
                }, function (index) {
                    layer.close(index);
                });
            });
            //批量删除
            $('#batchDel').click(function () {
                let _t = $(this);
                let arr = [];
                $('input:checkbox[name^="item_id"]:checked').each(function () {
                    arr.push($(this).val());
                });
                let ids = arr.length > 0 ? arr.join(',') : '';
                if (_this.isEmpty(ids)) {
                    _this.tipMsg('未选择任何行');
                    return false;
                }
                layer.confirm('确定要批量删除吗?', {btn: ['确定', '取消'], title: "提示"}, function (index) {
                    layer.close(index);
                    _this.postFun({'del_id': ids}, url, _t, 1, function (res) {
                        //延迟
                        layer.msg(res.msg, {icon: 1, time: 1000, shade: 0.4}, function () {
                            location.reload();
                        });
                    });
                }, function (index) {
                    layer.close(index);
                });
            });
        },

        //GET方法
        getFun: function (getData, url, callback) {
            $.ajax({
                type: "GET",
                url: _this.resetUrl(url),
                data: getData,
                dataType: "json",
                success: function (res) {
                    if (parseInt(res.code) !== 1) {
                        console.log(res);
                        _this.tipMsg(res.msg);
                    } else {
                        if (callback) {
                            callback(res);
                        }
                    }
                },
                error: function () {
                    _this.tipMsg('请求出错!');
                }
            });
        },

        //POST方法
        postFun: function (postData, url, btn, showLoading, callback, failCb) {
            let idx = null;
            _this.btnDisabled(btn, true);
            if (showLoading === 1) {
                idx = layer.load(2, {time: 20 * 1000});
            }
            $.ajax({
                type: "POST",
                url: _this.resetUrl(url),
                data: postData,
                dataType: "json",
                success: function (res) {
                    if (showLoading === 1) {
                        layer.close(idx);
                    }
                    if (parseInt(res.code) !== 1) {
                        console.log(res);
                        _this.tipMsg(res.msg);
                        _this.btnDisabled(btn, false);
                        if (failCb) {
                            failCb(res);
                        }
                    } else {
                        if (callback) {
                            callback(res);
                        } else {
                            _this.tipMsg(res.msg);
                            _this.btnDisabled(btn, false);
                        }
                    }
                },
                error: function (res) {
                    console.log(res);
                    if (showLoading === 1) {
                        layer.close(idx);
                    }
                    _this.tipMsg('请求出错!');
                    _this.btnDisabled(btn, false);
                }
            });
        },

        //单选与全选
        checkAllAndSingle: function () {
            form.on('checkbox(checkAllCk)', function (data) {
                let child = $('.checkSingleCk');
                child.each(function (index, item) {
                    item.checked = data.elem.checked;
                });
                form.render('checkbox');
            });
            form.on('checkbox(checkSingleCk)', function (data) {
                let allCk = $('#checkAllCk');
                let child = $('.checkSingleCk');
                let v = true;
                child.each(function () {
                    if (!$(this).prop("checked")) {
                        v = false;
                        return false;
                    }
                });
                allCk.prop("checked", v);
                form.render('checkbox');
            });
        },

        //监听表单提交
        listenToSubmit: function (reqUrl, close, btn, showLoading, goUrl) {
            form.on('submit(submit)', function (data) {
                //layer.msg('输入不正确！', {icon: 5, shift: 6}); //弹出表单错误提示方式
                console.log(data.field);
                if (btn == null) {
                    btn = $('#submitBtn');
                }
                _this.postFun(data.field, reqUrl, btn, showLoading, function (res) {
                    if (close) {
                        layer.msg(res.msg, {icon: 1, time: 2000, shade: 0.4}, function () {
                            xadmin.close();
                            xadmin.father_reload();
                            if (res.data.top_reload != undefined && res.data.top_reload != 'undefined') {
                                top.location.reload();
                            }
                        });
                    } else {
                        layer.msg(res.msg, {icon: 1, time: 2000, shade: 0.4}, function () {
                            if (res.data.top_reload != undefined && res.data.top_reload != 'undefined') {
                                top.location.reload();
                            } else {
                                location.href = goUrl;
                            }
                        });
                    }
                });
                return false;
            });
        },

        //禁用按钮
        btnDisabled: function (btn, state) {
            if (btn !== null) {
                if (state) {
                    btn.css({'pointer-events': 'none'});
                    btn.addClass("layui-btn-disabled").attr("disabled", true);
                } else {
                    btn.css({'pointer-events': ''});
                    btn.removeClass("layui-btn-disabled").attr("disabled", false);
                }
                form.render();
            }
        },

        //URL加随机参数
        resetUrl: function (url) {
            let i = url.indexOf("?");
            url = i === -1 ? url + "?t=" + Math.random() : url + "&t=" + Math.random();
            return url;
        },

        //一个简单的公用的上传按钮方法，需要的页面才可以使用.
        uploadImgSet: function (elem, acBtn, pathInput, prevImg, tipTag, tipTxt) {
            upload.render({
                elem: '#' + elem,
                url: '/backend/upload/index',
                auto: false,
                bindAction: '#' + acBtn,
                done: function (res) {
                    //console.log(res);
                    if (res.code === 1) {
                        if (pathInput) {
                            pathInput.val(res.data.path);
                        }
                        if (prevImg) {
                            prevImg.attr('src', res.data.path);
                        }
                        if (tipTag && tipTxt) {
                            tipTag.html(tipTxt);
                        }
                        layer.msg(res.msg);
                    } else {
                        layer.msg(res.msg);
                    }
                }
            });
        },

        //图片放大预览
        previewImg: function () {
            $(document).on('click', '.for-preview-zbig', function (data) {
                let imgSrc = $(this).attr('src');
                layer.open({
                    type: 1,
                    area: ['800px', '550px'],
                    title: false,
                    closeBtn: 0,
                    skin: 'layui-layer-nobg',
                    shadeClose: true,
                    content: '<img style="width:100%;height:100%;" class="layui-upload-img" src="' + imgSrc + '"/>',
                    scrollbar: false
                })
            });
        },

        //数字数组排序专用
        sortNumAsc: function (a, b) {
            return a - b;
        },

        //数字数组排序专用
        sortNumDesc: function (a, b) {
            return b - a;
        },

        //仅允许数字
        onlyAllowNumber: function () {
            $('.for-only-numbr').keyup(function () {
                $(this).val($(this).val().replace(/[^0-9.]/g, ''));
            }).bind('paste', function () {
                $(this).val($(this).val().replace(/[^0-9.]/g, ''));
            }).blur(function () {
                $(this).val($(this).val().replace(/[^0-9.]/g, ''));
            });
        }

    };

    let _this = obj;
    //输出接口
    exports('public', obj);
});
