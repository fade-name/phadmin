
// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

/**
 * ***模块***
 * 城市三级联动专用（请求数据方式）
 */
layui.define(['jquery', 'form', 'public'], function (exports) {
    let $ = layui.jquery;
    let form = layui.form;
    let pub = layui.public;
    let ele_province = null;
    let ele_city = null;
    let ele_district = null;

    let obj = {

        //赋元素名称值
        initSet: function (province_id, city_id, district_id) {
            ele_province = province_id;
            ele_city = city_id;
            ele_district = district_id;
        },

        //初始化城市下拉框
        //p，c，a对应于已选中的省市区的值，初选状态可传入0
        initCitySelect: function (p, c, a) {
            let province = $('#' + ele_province), city = $('#' + ele_city), district = $('#' + ele_district);
            _this.reqData(0, province, p);
            _this.bindSelectEvent(ele_province, city, district);
            if (p !== 0) {
                _this.reqData(p, city, c);
            }
            _this.bindSelectEvent(ele_city, null, district);
            if (c !== 0) {
                _this.reqData(c, district, a);
            }
        },

        //请求数据
        reqData: function (parent_id, ele, sid) {
            pub.getFun({parent_id: parent_id}, '/backend/common/area', function (res) {
                _this.removeEle(ele);
                _this.bindOptions(ele, res.data, sid);
                form.render('select');
            });
        },

        //清除下拉
        removeEle: function (ele) {
            ele.find("option").remove();
            let ops = "<option value='0'>" + "请选择" + "</option>";
            ele.append(ops);
        },

        //绑定下拉内容
        bindOptions: function (ele, data, sid) {
            $.each(data, function (key, item) {
                //key是数组下标，item是包含id，name的项.
                let selected = item.id == sid ? 'selected' : '';
                let optionStr = "<option value=" + item.id + " " + selected + ">" + item.long_name + "</option>";
                ele.append(optionStr);
            });
        },

        //绑定事件
        bindSelectEvent: function (eName, city, district) {
            form.on('select(' + eName + ')', function (data) {
                if (city && district) {
                    _this.reqData(data.value, city, '');
                    _this.removeEle(district);
                } else if (district) {
                    _this.reqData(data.value, district, '');
                }
                form.render('select');
            })
        }

    };

    let _this = obj;
    //输出接口
    exports('area_linkage', obj);
});
