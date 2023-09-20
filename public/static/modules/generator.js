
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
    let xArr = [];

    let obj = {

        //init
        initList: function () {
            pub.initRowDelete('/backend/generator/del');
            pub.checkAllAndSingle();
        },

        //init
        initAdd: function () {
            xArr[0] = null;
            _this.initListField(); //主数据表列表展示字段下拉框渲染
            _this.readDataTable('data_table', '选择要一键生成CRUD的表', 1); //读取表，渲染至对应元素
            _this.relationShowHide(); //是否关联数据的单选按钮方法
            _this.addRelateEvent(); //添加关联事件
            pub.listenToSubmit('/backend/generator/make', null, null, 1, '/backend/generator/index');
        },

        //列表展示字段下拉框初始化
        initListField: function () {
            window.xms_list_field = xmSelect.render({
                el: '#list_field',
                name: 'show_fields', //自定义下拉框的名称
                radio: false,
                language: 'zn',
                data: [], //初始化绑定数据
                on: function (data) {
                },
            });
        },

        //读取表
        readDataTable: function (eleId, defText, bType) {
            $.ajax({
                type: "GET",
                url: pub.resetUrl('/backend/common/read_table'),
                data: {},
                dataType: "json",
                success: function (res) {
                    if (parseInt(res.code) !== 1) {
                        console.log(res);
                        pub.tipMsg(res.msg);
                    } else {
                        let ele = $('#' + eleId);
                        ele.find("option").remove();
                        ele.append('<option value="">' + defText + '</option>');
                        $.each(res.data, function (key, item) {
                            let optionStr = "<option value=" + item.TABLE_NAME + ">" + item.TABLE_NAME + "</option>";
                            ele.append(optionStr);
                        });
                        form.render('select');
                        if (bType === 1) {
                            _this.tableChangeEvent();
                        } else {
                            _this.relationChangeEvent();
                        }
                    }
                },
                error: function () {
                    pub.tipMsg('请求出错!');
                }
            });
        },

        //表下拉框先择事件（该方法仅用于主数据表的下拉框）
        tableChangeEvent: function () {
            form.on('select(data_table)', function (data) {
                $.ajax({
                    type: "GET",
                    url: '/backend/common/read_fields',
                    data: {tb_name: data.value},
                    dataType: "json",
                    success: function (res) {
                        if (window.xms_list_field) {
                            window.xms_list_field.update({
                                data: res.data
                            });
                        }
                        $('#relate_container').empty();
                    },
                    error: function () {
                    }
                });
            });
        },

        //show,hide
        relationShowHide: function () {
            form.on('radio(relation)', function (data) {
                let tr = $('.relation_cfg');
                if (data.value == 1) {
                    tr.show();
                } else {
                    tr.hide();
                }
            });
        },

        //关联表添加按钮方法
        addRelateEvent: function () {
            $('#addRelationBtn').click(function () {
                let _t = $(this);
                let index = _t.attr('data_index');
                let idx = parseInt(index) + 1;
                let mainTable = $('#data_table').val();
                if (pub.isEmpty(mainTable)) {
                    layer.msg('请选择数据表');
                    return false;
                }
                _t.attr('data_index', idx);
                let html = ' <div class="layui-input-block" style="margin-left:0;"><div class="layui-inline"><label class="layui-form-label" style="width:36px;padding-left:2px;padding-right:2px;">关联表</label><div class="layui-input-inline" style="width:116px;"><select id="main_table_{IDX}" name="main_table[]" lay-verify="required" lay-search="" lay-filter="main_table" data-r-idx="{IDX}"><option value="">请选择</option></select></div></div><div class="layui-inline"><label class="layui-form-label" style="width:50px;padding-left:2px;padding-right:2px;">主表外键</label><div class="layui-input-inline" style="width:90px;"><select id="main_table_foreign_key_{IDX}" name="main_table_foreign_key[]" lay-filter="main_table_foreign_key"><option value="">请选择</option></select></div></div><div class="layui-inline"><label class="layui-form-label" style="width:60px;padding-left:2px;padding-right:2px;">关联表主键</label><div class="layui-input-inline" style="width:90px;"><select id="relation_primary_key_{IDX}" name="relation_primary_key[]" lay-filter="relation_primary_key"><option value="">请选择</option></select></div></div><div class="layui-inline"><label class="layui-form-label" style="width:60px;padding-left:2px;padding-right:2px;">关联名称键</label><div class="layui-input-inline" style="width:100px;"><select id="relation_title_key_{IDX}" name="relation_title_key[]" lay-filter="relation_title_key"><option value="">请选择</option></select></div></div><div class="layui-inline"><label class="layui-form-label" style="width:50px;padding-left:2px;padding-right:2px;">显示字段</label><div class="layui-input-inline" style="width:140px;"><div id="relation_show_field_{IDX}" class="xm-select-demo"></div></div></div><div class="layui-inline"><div class="layui-input-inline" style="width:60px;"><button type="button" class="layui-btn layui-btn-warm layui-btn-sm layui-btn-radius remove_relate_div">移除</button></div></div></div>';
                html = pub.replaceAllTo('{IDX}', idx, html);
                $('#relate_container').append(html);
                form.render('select');
                //优先xms
                _this.initRelateXms(idx);
                //data
                _this.readDataTable('main_table_' + idx, '请选择关联表', 2);
                //main.fields
                _this.readMainTbFields(mainTable, idx);
                //remove
                _this.removeEleEvent();
            });
        },

        //关联表选中事件方法
        relationChangeEvent: function () {
            form.on('select(main_table)', function (data) {
                let index = data.elem.getAttribute('data-r-idx');
                $.ajax({
                    type: "GET",
                    url: '/backend/common/read_fields',
                    data: {tb_name: data.value},
                    dataType: "json",
                    success: function (res) {
                        //单选下拉框ID
                        let ele = $('#relation_primary_key_' + index);
                        ele.find("option").remove();
                        //ele.append('<option value="">请选择</option>');
                        $.each(res.data, function (key, item) {
                            let optionStr = "<option value=" + item.value + ">" + item.name + "</option>";
                            ele.append(optionStr);
                        });
                        //单选下拉框title
                        let ele2 = $('#relation_title_key_' + index);
                        ele2.find("option").remove();
                        //ele2.append('<option value="">请选择</option>');
                        $.each(res.data, function (key, item) {
                            let optionStr = "<option value=" + item.value + ">" + item.name + "</option>";
                            ele2.append(optionStr);
                        });
                        //多选下拉框
                        let i = parseInt(index);
                        if (xArr[i]) {
                            xArr[i].update({
                                data: res.data
                            });
                        }
                        form.render('select');
                    },
                    error: function () {
                    }
                });
            });
        },

        //渲染XMS下拉框
        initRelateXms: function (index) {
            let i = parseInt(index);
            xArr[i] = xmSelect.render({
                el: '#relation_show_field_' + index,
                name: 'relation_fields[]', //自定义下拉框的名称
                radio: false,
                language: 'zn',
                data: [], //初始化绑定数据？
                on: function (data) {
                },
            });
        },

        //读取主表字段
        readMainTbFields: function (tbName, index) {
            $.ajax({
                type: "GET",
                url: '/backend/common/read_fields',
                data: {tb_name: tbName},
                dataType: "json",
                success: function (res) {
                    let ele = $('#main_table_foreign_key_' + index);
                    ele.find("option").remove();
                    //ele.append('<option value="">请选择</option>');
                    $.each(res.data, function (key, item) {
                        let optionStr = "<option value=" + item.value + ">" + item.name + "</option>";
                        ele.append(optionStr);
                    });
                    form.render('select');
                },
                error: function () {
                }
            });
        },

        //移除事件
        removeEleEvent: function () {
            $('.remove_relate_div').unbind('click').click(function () {
                $(this).parent('div').parent('div').parent('div').remove();
            });
        }

    };

    let _this = obj;
    //输出接口
    exports('generator', obj);
});
