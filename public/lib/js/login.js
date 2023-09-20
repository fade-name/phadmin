$(function () {
    layui.use('form', function () {
        let form = layui.form;
        //监听提交
        form.on('submit(login)', function (data) {
            let btn = $('#login_btn');
            btn.css({'pointer-events': 'none'});
            btn.addClass("layui-btn-disabled").attr("disabled", true);
            //console.log('发送数据===》：', data.field);
            $.ajax({
                type: "POST",
                url: "/backend/login",
                data: data.field,
                dataType: "json",
                success: function (res) {
                    //console.log('请求返回===》：', res);
                    if (res.code === 0) {
                        layer.msg(res.msg);
                        $('#captcha').val('');
                        $('#captcha_img').click();
                        btn.css({'pointer-events': ''});
                        btn.removeClass("layui-btn-disabled").attr("disabled", false);
                    } else {
                        top.location.href = res.data.url;
                    }
                },
                error: function (e) {
                    layer.msg('请求失败.');
                    $('#captcha').val('');
                    $('#captcha_img').click();
                    btn.css({'pointer-events': ''});
                    btn.removeClass("layui-btn-disabled").attr("disabled", false);
                }
            });
            return false;
        });
        $('#captcha_img').click(function () {
            let ts = new Date().getTime();
            $('#captcha_img').attr('src', '/captcha?t=' + ts);
            $('#captcha').focus();
        });
    });
});
