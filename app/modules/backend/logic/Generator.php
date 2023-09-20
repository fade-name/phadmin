<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Modules\Backend\Logic;

use Pha\Core\BaseLogic;
use Pha\Library\Tools;
use Pha\Library\Validator;
use Pha\Modules\Backend\Models\GeneratorLog;
use Pha\Modules\Backend\Models\MenuRule;
use Phalcon\Db\Enum;
use Phalcon\Di;
use function Composer\Autoload\includeFile;

class Generator extends BaseLogic
{

    //保护数据（不能生成的数据表）
    private static $_protect_table = ['admin', 'area', 'attachment', 'config', 'generator_log', 'menu_rule', 'role', 'user', 'user_group', 'user_token'];
    //参数
    protected static $_params;
    //主数据表字段（所有，含字段类型）
    protected static $_data_table_fields = [];
    //主数据表字段名数组（仅字段名的数组）
    protected static $_all_fields_arr = [];
    //主数据表列表展示字段（排除列表默认不展示的字段）
    protected static $_data_list_fields = [];
    //表的注释
    protected static $_data_table_comment;
    //主目录路径
    protected static $_main_path;
    //db.config
    protected static $_db_config;
    //统一的名称，控制器、模型、验证
    protected static $_unify_name;
    //主数据表主键字段
    protected static $_data_pri_key_field = 'id';
    //主数据表主键字段注释
    protected static $_data_pri_key_comment = 'ID';
    //主数据表作为下拉选择的名称的字段
    protected static $_data_selection_name_field = null;
    //下拉字段串
    protected static $_sel_ops_fields = [];
    //下拉父级字段名称
    protected static $_parent_id_field_name = 'parent_id';
    //是否无限级分类
    protected static $_infinite_level = 'false';
    //控制器名称空间
    protected static $_ctrl_name_space;
    //关联相关数据存储体
    protected static $_relation_data_model_block_body = '';
    protected static $_relation_data_block_body = '';
    protected static $_relation_data_var_block_body = '';
    //枚举数据字段变量存储
    protected static $_enum_data_var = '';
    //循环设置数据变量存储
    protected static $_foreach_set_data_var = '';
    //循环设置数据内体存储
    protected static $_foreach_set_data_body = '';
    //验证器数据值转换存储
    protected static $_data_value_conversion = '';
    //视图目录路径，views下的部分
    protected static $_v_path = null;
    //视图目录路径中对应数据表的名称
    protected static $_m_name = null;
    //字段类型归类
    protected static $_intTypArr = ['bigint', 'int', 'mediumint', 'smallint']; //整形数字
    protected static $_dateTypArr = ['date']; //日期
    protected static $_datetimeTypArr = ['datetime']; //日期时间
    protected static $_decimalTypArr = ['decimal', 'numeric', 'double', 'float']; //十进制数值
    protected static $_enumTypArr = ['enum']; //枚举
    protected static $_setTypArr = ['set']; //SET
    protected static $_textTypArr = ['text']; //TEXT
    protected static $_timeTypArr = ['time']; //TIME
    protected static $_timestampTypArr = ['timestamp']; //timestamp
    protected static $_tinyintTypArr = ['tinyint']; //tinyint
    protected static $_yearTypArr = ['year']; //year
    //列表标题替换串
    protected static $_page_list_til_fields_str = '';
    //列表字段展示替换串
    protected static $_page_list_data_fields_str = '';
    //colspan
    protected static $_page_list_colspan_num = 2;
    //添加数据字段替换串
    protected static $_page_add_fields_str = '';
    //编辑数据字段替换串
    protected static $_page_edit_fields_str = '';
    //下拉默认选中值替换串.add
    protected static $_add_selected_default_check = '';
    //下拉默认选中值替换串.edit
    protected static $_edit_selected_default_check = '';
    //xm.select
    protected static $_xm_select_script = '';
    //JS.name
    protected static $_script_mod_name;
    //ctrl.init
    protected static $_script_control_initialization = '';
    //third.party
    protected static $_introducing_third_party_styles = '';
    protected static $_introducing_third_party_script = '';
    protected static $_e_editor_script = '';
    //alias
    private static $_alias = ['b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n'];
    //exclude.cbx
    private static $_exclude_cbx = false;

    public static function making($params): bool
    {
        if (!\Pha\Modules\Backend\Validate\Generator::chk($params)) {
            return self::setErrReturn(\Pha\Modules\Backend\Validate\Generator::$error);
        }
        self::$_params = $params;
        $dbConfig = Di::getDefault()->getShared('database');
        $tb = str_replace($dbConfig->database->prefix, '', $params['data_table']);
        if (in_array($tb, self::$_protect_table)) {
            return self::setErrReturn('不能生成的数据表');
        }
        //基本
        self::baseData();
        if (empty(self::$_data_table_fields)) {
            return self::setErrReturn('未能获取主表字段');
        }
        //分析字段
        self::analyzeFields();
        //生成控制器
        self::makeController();
        //生成模型
        self::makeModel();
        //生成验证器
        self::makeValidate();
        //视图index
        self::makeViewIndex();
        //视图add
        self::makeViewAdd();
        //视图edit
        self::makeViewEdit();
        //JS脚本
        self::makeScript();
        //menu
        self::createMgrMenu();
        //record
        $relation_content = isset(self::$_params['main_table']) ? [
            'main_table' => self::$_params['main_table'],
            'main_table_foreign_key' => self::$_params['main_table_foreign_key'],
            'relation_primary_key' => self::$_params['relation_primary_key'],
            'relation_title_key' => self::$_params['relation_title_key'],
            'relation_fields' => self::$_params['relation_fields'],
        ] : [];
        $rData = [
            'data_table' => self::$_params['data_table'], 'custom_dir' => self::$_params['custom_dir'],
            'show_fields' => self::$_params['show_fields'], 'relation' => self::$_params['relation'],
            'relation_content' => json_encode($relation_content), 'discern_field' => json_encode(self::$_params['discern_field']),
            'm_type' => self::$_params['m_type'], 'create_time' => time()
        ];
        (new GeneratorLog())->createData($rData);

        return true;
    }

    //生成控制器
    private static function makeController()
    {
        $cPath = self::$_main_path . DS . 'controllers'; //控制器目录路径
        if (!empty(self::$_params['custom_dArr'])) {
            $cPath .= DS . strtolower(self::$_params['custom_dir']);
            self::mkdir($cPath);
            self::makeNamespaceConfig();
        } else {
            self::$_ctrl_name_space = 'Pha\Modules\Backend\Controllers';
        }
        //文件名及类名
        $className = self::$_unify_name . 'Controller';
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'controller.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //replace
        $code = str_replace('{{namespace}}', self::$_ctrl_name_space, $code);
        $code = str_replace('{{model}}', self::$_unify_name, $code);
        $code = str_replace('{{class_name}}', $className, $code);
        $code = str_replace('{{v_path}}', self::$_v_path, $code);
        $code = str_replace('{{m_name}}', self::$_m_name, $code);
        $code = str_replace('{{pri_key_field}}', self::$_data_pri_key_field, $code);
        //关联体
        if (empty(self::$_relation_data_model_block_body)) {
            $code = str_replace('{{relation_data_model_block}}', '//...', $code);
        } else {
            $code = str_replace('{{relation_data_model_block}}', self::$_relation_data_model_block_body, $code);
        }
        if (empty(self::$_relation_data_block_body)) {
            $code = str_replace('{{relation_data_block}}', '//...', $code);
        } else {
            $code = str_replace('{{relation_data_block}}', self::$_relation_data_block_body, $code);
        }
        if (empty(self::$_relation_data_var_block_body)) {
            $code = str_replace('{{relation_data_var_block}}', '//...', $code);
        } else {
            $code = str_replace('{{relation_data_var_block}}', self::$_relation_data_var_block_body, $code);
        }
        file_put_contents($cPath . DS . $className . '.php', $code);
    }

    //生成模型
    private static function makeModel()
    {
        $mPath = self::$_main_path . DS . 'models'; //模型目录路径
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'model.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //判断是否关联本表，若是则默认认为是无限级的下拉类型
        if (self::$_params['relation'] == 1 && in_array(self::$_params['data_table'], self::$_params['main_table'])) {
            $index = array_search(self::$_params['data_table'], self::$_params['main_table']);
            self::$_infinite_level = 'true';
            self::$_parent_id_field_name = self::$_params['main_table_foreign_key'][$index];
            //无限类型下拉需要加入父级字段
            self::$_sel_ops_fields[] = self::$_params['main_table_foreign_key'][$index];
        }
        //replace
        $code = str_replace('{{model}}', self::$_unify_name, $code);
        $code = str_replace('{{enum_data}}', self::$_enum_data_var, $code);
        $code = str_replace('{{foreach_set_data}}', self::$_foreach_set_data_var, $code);
        $code = str_replace('{{v_path}}', self::$_v_path, $code);
        $code = str_replace('{{m_name}}', self::$_m_name, $code);
        $code = str_replace('{{pri_key_field_only}}', self::$_data_pri_key_field, $code);
        //单独处理关联未关联的情况
        $code = self::relationHandle($code);
        //添加或编辑、删除的处理
        $code = self::addEditDelHandle($code);
        //下拉提供处理
        $code = self::dropDownHandle($code);
        $search_field = self::$_params['relation'] == 1 ? 'a.' : '';
        $search_field .= self::$_data_selection_name_field;
        $code = str_replace('{{search_var_field}}', $search_field, $code);
        $code = str_replace('{{infinite_level}}', self::$_infinite_level, $code);
        file_put_contents($mPath . DS . self::$_unify_name . '.php', $code);
        //生成初始的前端专用模型
        $mPath2 = APP_PATH . DS . 'common' . DS . 'models'; //模型目录路径
        $codeTp = self::$_main_path . DS . 'template' . DS . 'common_model.pholt';
        $code2 = file_get_contents($codeTp);
        $code2 = str_replace('{{model}}', self::$_unify_name, $code2);
        $code2 = str_replace('{{m_name}}', self::$_m_name, $code2);
        file_put_contents($mPath2 . DS . self::$_unify_name . '.php', $code2);
    }

    //生成验证器
    private static function makeValidate()
    {
        $vPath = self::$_main_path . DS . 'validate'; //验证器目录路径
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'validate.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //replace
        $code = str_replace('{{model}}', self::$_unify_name, $code);
        if (in_array('create_time', self::$_all_fields_arr)) {
            $code = str_replace('{{create_time_explanatory}}', '', $code);
        } else {
            $code = str_replace('{{create_time_explanatory}}', '//', $code);
        }
        if (empty(self::$_data_value_conversion)) {
            $code = str_replace('{{data_value_conversion}}', '//conversion', $code);
        } else {
            $code = str_replace('{{data_value_conversion}}', self::$_data_value_conversion, $code);
        }
        file_put_contents($vPath . DS . self::$_unify_name . '.php', $code);
    }

    //分析字段
    private static function analyzeFields()
    {
        $sfArr = !empty(self::$_params['show_fields']) ? explode(',', self::$_params['show_fields']) : [];
        //input.tip
        $tipTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'input_tip.pholt';
        $tipTemp = file_get_contents($tipTempPath);
        $dfTmp0 = str_replace('，', ',', self::$_params['discern_field'][0]);
        $dfTmp1 = str_replace('，', ',', self::$_params['discern_field'][1]);
        $dfTmp2 = str_replace('，', ',', self::$_params['discern_field'][2]);
        $dfTmp3 = str_replace('，', ',', self::$_params['discern_field'][3]);
        $dfTmp4 = str_replace('，', ',', self::$_params['discern_field'][4]);
        $dfTmp5 = str_replace('，', ',', self::$_params['discern_field'][5]);
        $dfTmp6 = str_replace('，', ',', self::$_params['discern_field'][6]);
        $dfTmp7 = str_replace('，', ',', self::$_params['discern_field'][7]);
        $dfTmp8 = str_replace('，', ',', self::$_params['discern_field'][8]);
        foreach (self::$_data_table_fields as $item) {
            self::$_exclude_cbx = false;
            if ($item['COLUMN_KEY'] == 'PRI' && $item['EXTRA'] == 'auto_increment') {
                continue;
            }
            if ($item['COLUMN_NAME'] == 'create_time') {
                continue;
            }
            if ($item['COLUMN_NAME'] == 'is_del') {
                continue;
            }
            $inputTipStr = '';
            //$kvArr = [];
            $flag = preg_match('/([\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-\(\)]+)([\:|：|\s])([a-zA-Z0-9\_\-]+([\=\:\：])[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-\(\)]+)(([\,\s][a-zA-Z0-9\_\-]+[\=\:\：][\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-\(\)]+)*)/iu', $item['COLUMN_COMMENT'], $matches);
            if ($flag) {
                $til = $matches[1];
                $kvArr = explode(',', $matches[3] . $matches[5]);
                $kvString = '';
                foreach ($kvArr as $kvItem) {
                    $aTmp = explode($matches[4], $kvItem);
                    //$iKey = Validator::isNumber($aTmp[0]) ? $aTmp[0] : "'" . $aTmp[0] . "'";
                    $kvString = empty($kvString)
                        ? "'" . $aTmp[0] . "' => '" . $aTmp[1] . "'"
                        : $kvString . ", '" . $aTmp[0] . "' => '" . $aTmp[1] . "'";
                }
                $kvString = rtrim(rtrim($kvString), ',');
                self::$_enum_data_var = empty(self::$_enum_data_var)
                    ? "protected \$_enum_data_" . $item['COLUMN_NAME'] . " = [" . $kvString . "];"
                    : self::$_enum_data_var . PHP_EOL . "    protected \$_enum_data_" . $item['COLUMN_NAME'] . " = [" . $kvString . "];";
            } else {
                $tmpComment = str_replace('，', ',', $item['COLUMN_COMMENT']);
                $tmpCa = explode(',', $tmpComment);
                //$til = $item['COLUMN_COMMENT'];
                $til = $tmpCa[0];
                $inputTipStr = isset($tmpCa[1]) && !empty($tmpCa[1]) ? $tmpCa[1] : $inputTipStr;
            }
            //字段分析
            if (in_array($item['DATA_TYPE'], self::$_intTypArr)) {
                if (($item['DATA_TYPE'] == 'bigint' || $item['DATA_TYPE'] == 'int') && Validator::str_end_with($item['COLUMN_NAME'], '_time')) {
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'datetime.pholt';
                    $defVal = '';
                    self::$_data_value_conversion = empty(self::$_data_value_conversion)
                        ? "\$params['" . $item['COLUMN_NAME'] . "'] = strtotime(\$params['" . $item['COLUMN_NAME'] . "']);"
                        : self::$_data_value_conversion . PHP_EOL . "        \$params['" . $item['COLUMN_NAME'] . "'] = strtotime(\$params['" . $item['COLUMN_NAME'] . "']);";
                    $editVal = "{{date('Y-m-d H:i:s',editData['" . $item['COLUMN_NAME'] . "'])}}";
                    self::$_script_control_initialization = empty(self::$_script_control_initialization)
                        ? "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'datetime'});"
                        : self::$_script_control_initialization . PHP_EOL . "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'datetime'});";
                } elseif (self::$_params['relation'] == 1 && in_array($item['COLUMN_NAME'], self::$_params['main_table_foreign_key'])) {
                    //如果是关联的，并且关联的字段是当前的字段
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'single_select.pholt';
                    $defVal = '';
                    $editVal = "{{editData['" . $item['COLUMN_NAME'] . "']}}";
                    $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_single_option.pholt';
                    $scriptTemp = file_get_contents($scriptOpsPath);
                    $idx = array_search($item['COLUMN_NAME'], self::$_params['main_table_foreign_key']);
                    $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
                    $scriptTemp = str_replace('{{relation_primary_key}}', self::$_params['relation_primary_key'][$idx], $scriptTemp);
                    $scriptTemp = str_replace('{{relation_title_key}}', self::$_params['relation_title_key'][$idx], $scriptTemp);
                    if (self::$_params['data_table'] == self::$_params['main_table'][$idx]) {
                        $scriptTemp = str_replace('{{v_path}}', self::$_v_path, $scriptTemp);
                        $scriptTemp = str_replace('{{m_name}}', self::$_m_name, $scriptTemp);
                    } else {
                        $mTmp = str_replace(self::$_db_config->database->prefix, '', self::$_params['main_table'][$idx]);
                        $record = GeneratorLog::findFirst(['conditions' => "data_table='" . self::$_params['main_table'][$idx] . "'", 'order' => 'id DESC']);
                        if ($record && !empty($record->custom_dir)) {
                            $scriptTemp = str_replace('{{v_path}}', $record->custom_dir . '/', $scriptTemp);
                        } else {
                            $scriptTemp = str_replace('{{v_path}}', '', $scriptTemp);
                        }
                        $scriptTemp = str_replace('{{m_name}}', $mTmp, $scriptTemp);
                    }
                    self::$_script_control_initialization = empty(self::$_script_control_initialization)
                        ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
                    self::$_add_selected_default_check = empty(self::$_add_selected_default_check)
                        ? "var ck_" . $item['COLUMN_NAME'] . " = '';"
                        : self::$_add_selected_default_check . PHP_EOL . "    var ck_" . $item['COLUMN_NAME'] . " = '';";
                    self::$_edit_selected_default_check = empty(self::$_edit_selected_default_check)
                        ? "var ck_" . $item['COLUMN_NAME'] . " = \"{{editData['" . $item['COLUMN_NAME'] . "']}}\";"
                        : self::$_edit_selected_default_check . PHP_EOL . "    var ck_" . $item['COLUMN_NAME'] . " = \"{{editData['" . $item['COLUMN_NAME'] . "']}}\";";
                } else {
                    if ($flag) {
                        self::foreachBody($item);
                    }
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'int.pholt';
                    $defVal = '0';
                    $editVal = "{{editData['" . $item['COLUMN_NAME'] . "']}}";
                }
                $inTemp = file_get_contents($inTempPath);
                $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                $inTemp = str_replace('{{field_required}}', 'required', $inTemp);
                $inTemp = str_replace('{{field_max_len}}', ($item['NUMERIC_PRECISION'] - 1), $inTemp);
                if (!empty($inputTipStr)) {
                    $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                    $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                } else {
                    $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                }
                $addText = str_replace('{{field_def_edi_value}}', $defVal, $inTemp);
                $editText = str_replace('{{field_def_edi_value}}', $editVal, $inTemp);
                self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
            } elseif (in_array($item['DATA_TYPE'], self::$_dateTypArr)) {
                self::date_processing($item, $til, $tipTemp);
            } elseif (in_array($item['DATA_TYPE'], self::$_datetimeTypArr)) {
                $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'datetime.pholt';
                $inTemp = file_get_contents($inTempPath);
                self::$_script_control_initialization = empty(self::$_script_control_initialization)
                    ? "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'datetime'});"
                    : self::$_script_control_initialization . PHP_EOL . "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'datetime'});";
                $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                if (!empty($inputTipStr)) {
                    $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                    $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                } else {
                    $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                }
                $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
                $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
                self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
            } elseif (in_array($item['DATA_TYPE'], self::$_decimalTypArr)) {
                $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'decimal.pholt';
                $inTemp = file_get_contents($inTempPath);
                $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                if (!empty($inputTipStr)) {
                    $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                    $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                } else {
                    $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                }
                $addText = str_replace('{{field_def_edi_value}}', '0.00', $inTemp);
                $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
                self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
            } elseif (in_array($item['DATA_TYPE'], self::$_enumTypArr)) {
                $opsString = '';
                $opsEString = '';
                $goRadio = false;
                if ($flag && stripos(',' . $dfTmp0 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                    //单选按钮
                    $goRadio = true;
                    self::foreachBody($item);
                    self::normal_enum_radio($item, $til, $matches, $tipTemp);
                } elseif ($flag) {
                    //设置了提示，已在匹配时处理，此无需处理$_enum_data_var.
                    $ksArr = explode(',', $matches[3] . $matches[5]);
                    foreach ($ksArr as $ksItem) {
                        $asTmp = explode($matches[4], $ksItem);
                        $opsString = empty($opsString)
                            ? "<option value=\"" . $asTmp[0] . "\">" . $asTmp[1] . "</option>"
                            : $opsString . PHP_EOL . "                                <option value=\"" . $asTmp[0] . "\">" . $asTmp[1] . "</option>";
                        $opsEString = empty($opsEString)
                            ? "<option value=\"" . $asTmp[0] . "\" {% if('" . $asTmp[0] . "'==editData['" . $item['COLUMN_NAME'] . "']) %}selected=\"selected\"{% endif %}>" . $asTmp[1] . "</option>"
                            : $opsEString . PHP_EOL . "                                <option value=\"" . $asTmp[0] . "\" {% if('" . $asTmp[0] . "'==editData['" . $item['COLUMN_NAME'] . "']) %}selected=\"selected\"{% endif %}>" . $asTmp[1] . "</option>";
                    }
                } else {
                    $sourceStr = str_replace("enum(", '', $item['COLUMN_TYPE']);
                    $sourceStr = str_replace(')', '', $sourceStr);
                    $sourceStr = str_replace("'", '', $sourceStr);
                    $aTmp = explode(',', $sourceStr);
                    $kvString = '';
                    foreach ($aTmp as $av) {
                        //$kv = Validator::isNumber($av) ? $av : "'" . $av . "'";
                        $kvString = empty($kvString)
                            ? "'" . $av . "' => '" . $av . "'"
                            : $kvString . ", '" . $av . "' => '" . $av . "'";
                        $opsString = empty($opsString)
                            ? "<option value=\"" . $av . "\">" . $av . "</option>"
                            : $opsString . PHP_EOL . "                                <option value=\"" . $av . "\">" . $av . "</option>";
                        $opsEString = empty($opsEString)
                            ? "<option value=\"" . $av . "\" {% if('" . $av . "'==editData['" . $item['COLUMN_NAME'] . "']) %}selected=\"selected\"{% endif %}>" . $av . "</option>"
                            : $opsEString . PHP_EOL . "                                <option value=\"" . $av . "\" {% if('" . $av . "'==editData['" . $item['COLUMN_NAME'] . "']) %}selected=\"selected\"{% endif %}>" . $av . "</option>";
                    }
                    $kvString = rtrim(rtrim($kvString), ',');
                    self::$_enum_data_var = empty(self::$_enum_data_var)
                        ? "protected \$_enum_data_" . $item['COLUMN_NAME'] . " = [" . $kvString . "];"
                        : self::$_enum_data_var . PHP_EOL . "    protected \$_enum_data_" . $item['COLUMN_NAME'] . " = [" . $kvString . "];";
                }
                if ($goRadio === false) {
                    self::foreachBody($item);
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'enum.pholt';
                    $inTemp = file_get_contents($inTempPath);
                    $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                    $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                    if (!empty($inputTipStr)) {
                        $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                        $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                    } else {
                        $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                    }
                    //$inTemp = str_replace('{{enum_value_list}}', $opsString, $inTemp);
                    $addText = str_replace('{{enum_value_list}}', $opsString, $inTemp);
                    $editText = str_replace('{{enum_value_list}}', $opsEString, $inTemp);
                    self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                    self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                }
            } elseif (in_array($item['DATA_TYPE'], self::$_setTypArr)) {
                self::$_exclude_cbx = true;
                //可多选（注：不使用下拉的形式，而是使用普通的多选复选框的形式）
                if ($flag) {
                    //用注释所配置的项所为下拉项,多选
                    $kyArr = explode(',', $matches[3] . $matches[5]);
                    $ef = true;
                } else {
                    //用设置的可选项所为下拉项,多选
                    $sourceStr = str_replace("set(", '', $item['COLUMN_TYPE']);
                    $sourceStr = str_replace(')', '', $sourceStr);
                    $sourceStr = str_replace("'", '', $sourceStr);
                    $kyArr = explode(',', $sourceStr);
                    $ef = false;
                }
                $opsString = '';
                $opsEString = '';
                foreach ($kyArr as $kyItem) {
                    if ($ef) {
                        $arTmp = explode($matches[4], $kyItem);
                    } else {
                        $arTmp[0] = $kyItem;
                        $arTmp[1] = $kyItem;
                    }
                    $opsString = empty($opsString)
                        ? "<input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $arTmp[0] . "\" title=\"" . $arTmp[1] . "\">"
                        : $opsString . PHP_EOL . "                            <input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $arTmp[0] . "\" title=\"" . $arTmp[1] . "\">";
                    $opsEString = empty($opsEString)
                        ? "<input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $arTmp[0] . "\" title=\"" . $arTmp[1] . "\" {% if(stripos(','~editData['" . $item['COLUMN_NAME'] . "']~',', ','~'" . $arTmp[0] . "'~',') !== false) %}checked=\"checked\"{% endif %}>"
                        : $opsEString . PHP_EOL . "                            <input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $arTmp[0] . "\" title=\"" . $arTmp[1] . "\" {% if(stripos(','~editData['" . $item['COLUMN_NAME'] . "']~',', ','~'" . $arTmp[0] . "'~',') !== false) %}checked=\"checked\"{% endif %}>";
                }
                $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'checkbox.pholt';
                $inTemp = file_get_contents($inTempPath);
                $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                if (!empty($inputTipStr)) {
                    $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                    $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                } else {
                    $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                }
                $addText = str_replace('{{checkbox_value_list}}', $opsString, $inTemp);
                $editText = str_replace('{{checkbox_value_list}}', $opsEString, $inTemp);
                self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                self::$_data_value_conversion = empty(self::$_data_value_conversion)
                    ? "\$params['" . $item['COLUMN_NAME'] . "'] = implode(',', \$params['" . $item['COLUMN_NAME'] . "']);"
                    : self::$_data_value_conversion . PHP_EOL . "        \$params['" . $item['COLUMN_NAME'] . "'] = implode(',', \$params['" . $item['COLUMN_NAME'] . "']);";
            } elseif (in_array($item['DATA_TYPE'], self::$_textTypArr)) {
                //给kindeditor编辑器
                self::kind_editor($item, $til);
            } elseif (in_array($item['DATA_TYPE'], self::$_timeTypArr)) {
                //时间选择，没有年月日
                $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'time.pholt';
                $inTemp = file_get_contents($inTempPath);
                self::$_script_control_initialization = empty(self::$_script_control_initialization)
                    ? "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'time'});"
                    : self::$_script_control_initialization . PHP_EOL . "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'time'});";
                $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                if (!empty($inputTipStr)) {
                    $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                    $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                } else {
                    $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                }
                $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
                $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
                self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
            } elseif (in_array($item['DATA_TYPE'], self::$_timestampTypArr)) {
                //时间戮格式，但选择时也是日期时间的选择
                self::datetime_processing($item, $til, $tipTemp, false); //timestamp也是直接日期时间格式的
            } elseif (in_array($item['DATA_TYPE'], self::$_tinyintTypArr)) {
                if ($flag) {
                    self::foreachBody($item);
                    if (stripos(',' . $dfTmp8 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        $ksArr = explode(',', $matches[3] . $matches[5]);
                        if (count($ksArr) == 2) {
                            //开关
                            $swTxtA = [];
                            foreach ($ksArr as $ksItem) {
                                $asTmp = explode($matches[4], $ksItem);
                                $swTxtA[] = $asTmp[1];
                            }
                            $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'switch.pholt';
                            $inTemp = file_get_contents($inTempPath);
                            $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                            $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                            $inTemp = str_replace('{{field_til0}}', $swTxtA[1], $inTemp);
                            $inTemp = str_replace('{{field_til1}}', $swTxtA[0], $inTemp);
                            $addText = str_replace('{{checked}}', '', $inTemp);
                            $editText = str_replace('{{checked}}', "{% if(1==editData['" . $item['COLUMN_NAME'] . "']) %}checked=\"\"{% endif %}", $inTemp);
                            $addText = str_replace('{{field_def_edi_value}}', '0', $addText);
                            $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $editText);
                            self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                            self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                            $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_switch.pholt';
                            $scriptTemp = file_get_contents($scriptOpsPath);
                            $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
                            self::$_script_control_initialization = empty(self::$_script_control_initialization)
                                ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
                        } else {
                            self::normal_enum_radio($item, $til, $matches, $tipTemp);
                        }
                    } else {
                        self::normal_enum_radio($item, $til, $matches, $tipTemp);
                    }
                } else {
                    //普通输入框
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'tinyint.pholt';
                    $inTemp = file_get_contents($inTempPath);
                    $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                    $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                    $inTemp = str_replace('{{field_required}}', '', $inTemp);
                    if (!empty($inputTipStr)) {
                        $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                        $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                    } else {
                        $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                    }
                    $addText = str_replace('{{field_def_edi_value}}', '0', $inTemp);
                    $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
                    self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                    self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                }
            } elseif (in_array($item['DATA_TYPE'], self::$_yearTypArr)) {
                if ($flag) {
                    self::foreachBody($item);
                    self::normal_enum_dropdown($item, $til, $matches, $tipTemp);
                } else {
                    //年份选择
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'year.pholt';
                    $inTemp = file_get_contents($inTempPath);
                    self::$_script_control_initialization = empty(self::$_script_control_initialization)
                        ? "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'year'});"
                        : self::$_script_control_initialization . PHP_EOL . "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'year'});";
                    $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                    $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                    if (!empty($inputTipStr)) {
                        $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                        $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                    } else {
                        $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                    }
                    $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
                    $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
                    self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                    self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                }
            } else {
                //string
                if ($flag) {
                    if (stripos(',' . $dfTmp0 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //单选按钮
                        self::foreachBody($item);
                        self::normal_enum_radio($item, $til, $matches, $tipTemp);
                    } elseif (stripos(',' . $dfTmp1 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        self::$_exclude_cbx = true;
                        //多选复选框
                        $opsString = '';
                        $opsEString = '';
                        $ksArr = explode(',', $matches[3] . $matches[5]);
                        foreach ($ksArr as $ksItem) {
                            $asTmp = explode($matches[4], $ksItem);
                            $opsString = empty($opsString)
                                ? "<input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\">"
                                : $opsString . PHP_EOL . "                            <input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\">";
                            $opsEString = empty($opsEString)
                                ? "<input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\" {% if(stripos(','~editData['" . $item['COLUMN_NAME'] . "']~',', ','~'" . $asTmp[0] . "'~',') !== false) %}checked=\"checked\"{% endif %}>"
                                : $opsEString . PHP_EOL . "                            <input type=\"checkbox\" name=\"" . $item['COLUMN_NAME'] . "[]\" lay-skin=\"primary\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\" {% if(stripos(','~editData['" . $item['COLUMN_NAME'] . "']~',', ','~'" . $asTmp[0] . "'~',') !== false) %}checked=\"checked\"{% endif %}>";
                        }
                        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'checkbox.pholt';
                        $inTemp = file_get_contents($inTempPath);
                        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                        if (!empty($inputTipStr)) {
                            $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                            $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                        } else {
                            $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                        }
                        $addText = str_replace('{{checkbox_value_list}}', $opsString, $inTemp);
                        $editText = str_replace('{{checkbox_value_list}}', $opsEString, $inTemp);
                        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                        self::$_data_value_conversion = empty(self::$_data_value_conversion)
                            ? "\$params['" . $item['COLUMN_NAME'] . "'] = implode(',', \$params['" . $item['COLUMN_NAME'] . "']);"
                            : self::$_data_value_conversion . PHP_EOL . "        \$params['" . $item['COLUMN_NAME'] . "'] = implode(',', \$params['" . $item['COLUMN_NAME'] . "']);";
                    } else {
                        //普通下拉
                        self::foreachBody($item);
                        self::normal_enum_dropdown($item, $til, $matches, $tipTemp);
                    }
                } elseif (self::$_params['relation'] == 1 && in_array($item['COLUMN_NAME'], self::$_params['main_table_foreign_key'])) {
                    //下拉多选的类型
                    $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'multiple_choice.pholt';
                    $inTemp = file_get_contents($inTempPath);
                    self::$_add_selected_default_check = empty(self::$_add_selected_default_check)
                        ? "window.xms_" . $item['COLUMN_NAME'] . " = null;"
                        : self::$_add_selected_default_check . PHP_EOL . "    window.xms_" . $item['COLUMN_NAME'] . " = null;";
                    self::$_edit_selected_default_check = empty(self::$_edit_selected_default_check)
                        ? "window.xms_" . $item['COLUMN_NAME'] . " = null;"
                        : self::$_edit_selected_default_check . PHP_EOL . "    window.xms_" . $item['COLUMN_NAME'] . " = null;";
                    $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                    $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                    $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_xms_remote_method.pholt';
                    $scriptTemp = file_get_contents($scriptOpsPath);
                    $idx = array_search($item['COLUMN_NAME'], self::$_params['main_table_foreign_key']);
                    $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
                    if (self::$_params['data_table'] == self::$_params['main_table'][$idx]) {
                        $scriptTemp = str_replace('{{v_path}}', self::$_v_path, $scriptTemp);
                        $scriptTemp = str_replace('{{m_name}}', self::$_m_name, $scriptTemp);
                    } else {
                        $mTmp = str_replace(self::$_db_config->database->prefix, '', self::$_params['main_table'][$idx]);
                        $record = GeneratorLog::findFirst(['conditions' => "data_table='" . self::$_params['main_table'][$idx] . "'", 'order' => 'id DESC']);
                        if ($record && !empty($record->custom_dir)) {
                            $scriptTemp = str_replace('{{v_path}}', $record->custom_dir . '/', $scriptTemp);
                        } else {
                            $scriptTemp = str_replace('{{v_path}}', '', $scriptTemp);
                        }
                        $scriptTemp = str_replace('{{m_name}}', $mTmp, $scriptTemp);
                    }
                    $scriptTemp = str_replace('{{init_data}}', "init_data_" . $item['COLUMN_NAME'], $scriptTemp);
                    self::$_script_control_initialization = empty(self::$_script_control_initialization)
                        ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
                    $rmTmp = str_replace(self::$_db_config->database->prefix, '', self::$_params['main_table'][$idx]);
                    $rmTmp = Tools::camelize($rmTmp, '_', true);
                    $use_namespace = "use Pha\Modules\Backend\Models\\" . $rmTmp . ";";
                    if (empty(self::$_relation_data_model_block_body) || strpos(self::$_relation_data_model_block_body, $use_namespace) === false) {
                        self::$_relation_data_model_block_body = empty(self::$_relation_data_model_block_body)
                            ? $use_namespace
                            : self::$_relation_data_model_block_body . PHP_EOL . $use_namespace;
                    }
                    self::$_relation_data_block_body = empty(self::$_relation_data_block_body)
                        ? "\$init_data_" . $item['COLUMN_NAME'] . " = (new " . $rmTmp . "())->getXmsOptions(\$this->_ap['get']);"
                        : self::$_relation_data_block_body . PHP_EOL . "        \$init_data_" . $item['COLUMN_NAME'] . " = (new " . $rmTmp . "())->getXmsOptions(\$this->_ap['get']);";
                    self::$_relation_data_block_body .= PHP_EOL . "        \$s_" . $item['COLUMN_NAME'] . " = str_replace('\"value\"', 'value', str_replace('\"name\"', 'name', json_encode(\$init_data_" . $item['COLUMN_NAME'] . ")));";
                    self::$_relation_data_var_block_body = empty(self::$_relation_data_var_block_body)
                        ? "'for_init_" . $item['COLUMN_NAME'] . "' => \$s_" . $item['COLUMN_NAME'] . ","
                        : self::$_relation_data_var_block_body . PHP_EOL . "            'for_init_" . $item['COLUMN_NAME'] . "' => \$s_" . $item['COLUMN_NAME'] . ",";
                    self::$_add_selected_default_check = empty(self::$_add_selected_default_check)
                        ? "var init_data_" . $item['COLUMN_NAME'] . " = [];"
                        : self::$_add_selected_default_check . PHP_EOL . "    var init_data_" . $item['COLUMN_NAME'] . " = [];";
                    self::$_edit_selected_default_check = empty(self::$_edit_selected_default_check)
                        ? "var init_data_" . $item['COLUMN_NAME'] . " = {{for_init_" . $item['COLUMN_NAME'] . "}};"
                        : self::$_edit_selected_default_check . PHP_EOL . "    var init_data_" . $item['COLUMN_NAME'] . " = {{for_init_" . $item['COLUMN_NAME'] . "}};";
                    self::$_add_selected_default_check = empty(self::$_add_selected_default_check)
                        ? "var ck_" . $item['COLUMN_NAME'] . " = [];"
                        : self::$_add_selected_default_check . PHP_EOL . "    var ck_" . $item['COLUMN_NAME'] . " = [];";
                    self::$_edit_selected_default_check = empty(self::$_edit_selected_default_check)
                        ? "var ck_" . $item['COLUMN_NAME'] . " = \"{{editData['" . $item['COLUMN_NAME'] . "']}}\".split(',');"
                        : self::$_edit_selected_default_check . PHP_EOL . "    var ck_" . $item['COLUMN_NAME'] . " = \"{{editData['" . $item['COLUMN_NAME'] . "']}}\".split(',');";
                    self::$_xm_select_script = "<script src=\"/lib/xm_select/xm-select.js\" type=\"text/javascript\"></script>";
                    self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $inTemp : self::$_page_add_fields_str . PHP_EOL . $inTemp;
                    self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $inTemp : self::$_page_edit_fields_str . PHP_EOL . $inTemp;
                } else {
                    if (stripos(',' . $dfTmp2 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //单图上传
                        self::single_image_upload_processing($item, $til);
                    } elseif (stripos(',' . $dfTmp3 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //多图上传
                        self::multi_images_upload_processing($item, $til);
                    } elseif (stripos(',' . $dfTmp4 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //单文件上传
                        self::single_file_upload_processing($item, $til);
                    } elseif (stripos(',' . $dfTmp5 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //多文件上传
                        self::multi_file_upload_processing($item, $til);
                    } elseif (stripos(',' . $dfTmp6 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //日期格式选择
                        self::date_processing($item, $til, $tipTemp);
                    } elseif (stripos(',' . $dfTmp7 . ',', ',' . $item['COLUMN_NAME'] . ',') !== false) {
                        //日期时间格式选择
                        self::datetime_processing($item, $til, $tipTemp);
                    } else {
                        //根据后缀来，设置如图片上传，文件上传，头像上传
                        if (Validator::str_end_with($item['COLUMN_NAME'], '_image') || Validator::str_end_with($item['COLUMN_NAME'], '_avatar')) {
                            //单图上传
                            self::single_image_upload_processing($item, $til);
                        } elseif (Validator::str_end_with($item['COLUMN_NAME'], '_images')) {
                            //多图上传
                            self::multi_images_upload_processing($item, $til);
                        } elseif (Validator::str_end_with($item['COLUMN_NAME'], '_file')) {
                            //单文件上传
                            self::single_file_upload_processing($item, $til);
                        } elseif (Validator::str_end_with($item['COLUMN_NAME'], '_files')) {
                            //多文件上传
                            self::multi_file_upload_processing($item, $til);
                        } elseif (Validator::str_end_with($item['COLUMN_NAME'], '_content')) {
                            //富文本编辑器
                            self::kind_editor($item, $til);
                        } else {
                            //普通输入框
                            $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'text.pholt';
                            $inTemp = file_get_contents($inTempPath);
                            $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
                            $inTemp = str_replace('{{field_text}}', $til, $inTemp);
                            $inTemp = str_replace('{{field_required}}', '', $inTemp);
                            $m = max($item['CHARACTER_MAXIMUM_LENGTH'] - 3, 1);
                            $inTemp = str_replace('{{field_max_len}}', $m, $inTemp);
                            if (!empty($inputTipStr)) {
                                $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
                                $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
                            } else {
                                $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
                            }
                            $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
                            $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
                            self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
                            self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
                        }
                    }
                }
            }
            //列表标题、字段展示内容
            $tFieldName = self::$_params['relation'] == 1 ? 'a_' . $item['COLUMN_NAME'] : $item['COLUMN_NAME'];
            if (!empty($sfArr)) {
                if (in_array($item['COLUMN_NAME'], $sfArr)) {
                    self::$_page_list_til_fields_str = empty(self::$_page_list_til_fields_str) ? "<th>" . $til . "</th>" : self::$_page_list_til_fields_str . PHP_EOL . "                            <th>" . $til . "</th>";
                    self::listRowValueStr($item['DATA_TYPE'], $tFieldName, $flag, $matches);
                    self::$_page_list_colspan_num++;
                }
            } else {
                self::$_page_list_til_fields_str = empty(self::$_page_list_til_fields_str) ? "<th>" . $til . "</th>" : self::$_page_list_til_fields_str . PHP_EOL . "                            <th>" . $til . "</th>";
                self::listRowValueStr($item['DATA_TYPE'], $tFieldName, $flag, $matches);
                self::$_page_list_colspan_num++;
            }
        }
        if (self::$_params['relation'] == 1) {
            $db = Di::getDefault()->getShared('db');
            foreach (self::$_params['relation_fields'] as $iK => $iParam) {
                if (!empty($iParam)) {
                    $wStr = "'" . str_replace(',', "','", $iParam) . "'";
                    $wSql = "SELECT COLUMN_COMMENT FROM information_schema.`COLUMNS` "
                        . "WHERE TABLE_SCHEMA = '" . self::$_db_config->database->dbname . "' "
                        . "AND TABLE_NAME = '" . self::$_params['main_table'][$iK] . "' "
                        . "AND COLUMN_NAME IN (" . $wStr . ") "
                        . "ORDER BY ORDINAL_POSITION ASC";
                    $fData = $db->query($wSql);
                    //筛选只要字符键名的数据，不要数字下标的数据
                    $fData->setFetchMode(Enum::FETCH_ASSOC);
                    $fV = $fData->fetchAll();
                    foreach ($fV as $fvi) {
                        self::$_page_list_til_fields_str = empty(self::$_page_list_til_fields_str) ? "<th>" . $fvi['COLUMN_COMMENT'] . "</th>" : self::$_page_list_til_fields_str . PHP_EOL . "                            <th>" . $fvi['COLUMN_COMMENT'] . "</th>";
                        self::$_page_list_colspan_num++;
                    }
                    $fTmp = self::$_alias[$iK] . '_' . str_replace(',', ',' . self::$_alias[$iK] . '_', $iParam);
                    $faTmp = explode(',', $fTmp);
                    foreach ($faTmp as $fdName) {
                        self::$_page_list_data_fields_str = empty(self::$_page_list_data_fields_str) ? "<td>{{item['" . $fdName . "']}}</td>" : self::$_page_list_data_fields_str . PHP_EOL . "                            <td>{{item['" . $fdName . "']}}</td>";
                    }
                }
            }
        }
        if (!empty(self::$_foreach_set_data_body)) {
            self::$_foreach_set_data_var = "foreach (\$data['list'] as \$key => &\$datum) {" . PHP_EOL . "            " . self::$_foreach_set_data_body . PHP_EOL . "        }";
        } else {
            self::$_foreach_set_data_var = "//foreach";
        }
    }

    //radio
    private static function normal_enum_radio($item, $til, $matches, $tipTemp = '')
    {
        $opsString = '';
        $opsEString = '';
        $ksArr = explode(',', $matches[3] . $matches[5]);
        $ci = 0;
        foreach ($ksArr as $ksItem) {
            $asTmp = explode($matches[4], $ksItem);
            $ck = $ci === 0 ? "checked=\"checked\"" : "";
            $opsString = empty($opsString)
                ? "<input type=\"radio\" name=\"" . $item['COLUMN_NAME'] . "\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\" " . $ck . ">"
                : $opsString . PHP_EOL . "                            <input type=\"radio\" name=\"" . $item['COLUMN_NAME'] . "\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\" " . $ck . ">";
            $opsEString = empty($opsEString)
                ? "<input type=\"radio\" name=\"" . $item['COLUMN_NAME'] . "\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\" {% if('" . $asTmp[0] . "'==editData['" . $item['COLUMN_NAME'] . "']) %}checked=\"checked\"{% endif %}>"
                : $opsEString . PHP_EOL . "                            <input type=\"radio\" name=\"" . $item['COLUMN_NAME'] . "\" value=\"" . $asTmp[0] . "\" title=\"" . $asTmp[1] . "\" {% if('" . $asTmp[0] . "'==editData['" . $item['COLUMN_NAME'] . "']) %}checked=\"checked\"{% endif %}>";
            $ci++;
        }
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'radio.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        if (empty($tipTemp)) {
            $tipTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'input_tip.pholt';
            $tipTemp = file_get_contents($tipTempPath);
        }
        if (!empty($inputTipStr)) {
            $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
            $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
        } else {
            $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
        }
        $addText = str_replace('{{radio_value_list}}', $opsString, $inTemp);
        $editText = str_replace('{{radio_value_list}}', $opsEString, $inTemp);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
    }

    //select
    private static function normal_enum_dropdown($item, $til, $matches, $tipTemp = '')
    {
        $opsString = '';
        $opsEString = '';
        $ksArr = explode(',', $matches[3] . $matches[5]);
        foreach ($ksArr as $ksItem) {
            $asTmp = explode($matches[4], $ksItem);
            $opsString = empty($opsString)
                ? "<option value=\"" . $asTmp[0] . "\">" . $asTmp[1] . "</option>"
                : $opsString . PHP_EOL . "                                <option value=\"" . $asTmp[0] . "\">" . $asTmp[1] . "</option>";
            $opsEString = empty($opsEString)
                ? "<option value=\"" . $asTmp[0] . "\" {% if('" . $asTmp[0] . "'==editData['" . $item['COLUMN_NAME'] . "']) %}selected=\"selected\"{% endif %}>" . $asTmp[1] . "</option>"
                : $opsEString . PHP_EOL . "                                <option value=\"" . $asTmp[0] . "\" {% if('" . $asTmp[0] . "'==editData['" . $item['COLUMN_NAME'] . "']) %}selected=\"selected\"{% endif %}>" . $asTmp[1] . "</option>";
        }
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'enum.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        if (empty($tipTemp)) {
            $tipTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'input_tip.pholt';
            $tipTemp = file_get_contents($tipTempPath);
        }
        if (!empty($inputTipStr)) {
            $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
            $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
        } else {
            $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
        }
        $addText = str_replace('{{enum_value_list}}', $opsString, $inTemp);
        $editText = str_replace('{{enum_value_list}}', $opsEString, $inTemp);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
    }

    //date
    private static function date_processing($item, $til, $tipTemp = '')
    {
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'date.pholt';
        $inTemp = file_get_contents($inTempPath);
        self::$_script_control_initialization = empty(self::$_script_control_initialization)
            ? "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "'});"
            : self::$_script_control_initialization . PHP_EOL . "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "'});";
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        if (empty($tipTemp)) {
            $tipTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'input_tip.pholt';
            $tipTemp = file_get_contents($tipTempPath);
        }
        if (!empty($inputTipStr)) {
            $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
            $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
        } else {
            $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
        }
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
    }

    //datetime
    private static function datetime_processing($item, $til, $tipTemp = '', $convert = false)
    {
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'datetime.pholt';
        if ($convert) {
            self::$_data_value_conversion = empty(self::$_data_value_conversion)
                ? "\$params['" . $item['COLUMN_NAME'] . "'] = strtotime(\$params['" . $item['COLUMN_NAME'] . "']);"
                : self::$_data_value_conversion . PHP_EOL . "        \$params['" . $item['COLUMN_NAME'] . "'] = strtotime(\$params['" . $item['COLUMN_NAME'] . "']);";
        }
        self::$_script_control_initialization = empty(self::$_script_control_initialization)
            ? "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'datetime'});"
            : self::$_script_control_initialization . PHP_EOL . "            laydate.render({elem: '#" . $item['COLUMN_NAME'] . "', type: 'datetime'});";
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        if (empty($tipTemp)) {
            $tipTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'input_tip.pholt';
            $tipTemp = file_get_contents($tipTempPath);
        }
        if (!empty($inputTipStr)) {
            $tipText = str_replace('{{input_tip_text}}', $inputTipStr, $tipTemp);
            $inTemp = str_replace('{{field_input_tip}}', $tipText, $inTemp);
        } else {
            $inTemp = str_replace('{{field_input_tip}}', '', $inTemp);
        }
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        if ($convert) {
            $editText = str_replace('{{field_def_edi_value}}', "{{date('Y-m-d H:i:s',editData['" . $item['COLUMN_NAME'] . "'])}}", $inTemp);
        } else {
            $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        }
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
    }

    //image
    private static function single_image_upload_processing($item, $til)
    {
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'single_image_upload.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        $addText = str_replace('{{prev_def_image}}', '/images/img_prev.jpg', $addText);
        $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        $editText = str_replace('{{prev_def_image}}', "{% if not is_empty(editData['" . $item['COLUMN_NAME'] . "']) %}{{editData['" . $item['COLUMN_NAME'] . "']}}{% else %}/images/img_prev.jpg{% endif %}", $editText);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
        $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_single_image_upload.pholt';
        $scriptTemp = file_get_contents($scriptOpsPath);
        $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
        self::$_script_control_initialization = empty(self::$_script_control_initialization)
            ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
    }

    //images
    private static function multi_images_upload_processing($item, $til)
    {
        if (empty(self::$_introducing_third_party_styles)) {
            self::$_introducing_third_party_styles = "<link rel=\"stylesheet\" href=\"/style/css/multiUpStyle.css\">";
        } else {
            if (stripos(self::$_introducing_third_party_styles, 'style/css/multiUpStyle.css') === false) {
                self::$_introducing_third_party_styles = self::$_introducing_third_party_styles . PHP_EOL . "    <link rel=\"stylesheet\" href=\"/style/css/multiUpStyle.css\">";
            }
        }
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'multi_image_upload.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
        $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_multi_image_upload.pholt';
        $scriptTemp = file_get_contents($scriptOpsPath);
        $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
        if (stripos(self::$_script_control_initialization, 'let miu_img_up_c = layui.multiImgUpload;') === false) {
            self::$_script_control_initialization = empty(self::$_script_control_initialization)
                ? "            let miu_img_up_c = layui.multiImgUpload;" : self::$_script_control_initialization . PHP_EOL . "            let miu_img_up_c = layui.multiImgUpload;";
        }
        self::$_script_control_initialization = str_replace("miu_img_up_c.uploadEventInitBind()", "//miu_img_up_c.uploadEventInitBind()", self::$_script_control_initialization);
        self::$_script_control_initialization = empty(self::$_script_control_initialization)
            ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
    }

    //files
    private static function single_file_upload_processing($item, $til)
    {
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'single_upload.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        $addText = str_replace('{{edi_upd_path_tips}}', '', $addText);
        $editText = str_replace('{{edi_upd_path_tips}}', "{% if not is_empty(editData['" . $item['COLUMN_NAME'] . "']) %}路径：{{editData['" . $item['COLUMN_NAME'] . "']}}{% endif %}", $editText);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
        $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_single_upload.pholt';
        $scriptTemp = file_get_contents($scriptOpsPath);
        $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
        self::$_script_control_initialization = empty(self::$_script_control_initialization)
            ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
    }

    //editor
    private static function kind_editor($item, $til)
    {
        if (empty(self::$_introducing_third_party_styles)) {
            self::$_introducing_third_party_styles = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/lib/kindeditor/themes/default/default.css\"/>";
        } else {
            if (stripos(self::$_introducing_third_party_styles, 'lib/kindeditor/themes/default/default.css') === false) {
                self::$_introducing_third_party_styles = self::$_introducing_third_party_styles . PHP_EOL . "    <link rel=\"stylesheet\" type=\"text/css\" href=\"/lib/kindeditor/themes/default/default.css\"/>";
            }
        }
        if (empty(self::$_introducing_third_party_script)) {
            self::$_introducing_third_party_script = "<script src=\"/lib/kindeditor/kindeditor-all-min.js\"></script>";
            self::$_introducing_third_party_script .= PHP_EOL . "    <script src=\"/lib/kindeditor/lang/zh-CN.js\"></script>";
        } else {
            if (stripos(self::$_introducing_third_party_script, 'lib/kindeditor/kindeditor-all-min.js') === false) {
                self::$_introducing_third_party_script = self::$_introducing_third_party_script . PHP_EOL . "    <script src=\"/lib/kindeditor/kindeditor-all-min.js\"></script>";
                self::$_introducing_third_party_script .= PHP_EOL . "    <script src=\"/lib/kindeditor/lang/zh-CN.js\"></script>";
            }
        }
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'content.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
        $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_kindeditor.pholt';
        $scriptTemp = file_get_contents($scriptOpsPath);
        $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
        self::$_e_editor_script = empty(self::$_e_editor_script) ? $scriptTemp : self::$_e_editor_script . PHP_EOL . $scriptTemp;
    }

    //多文件
    private static function multi_file_upload_processing($item, $til)
    {
        $inTempPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'multi_upload.pholt';
        $inTemp = file_get_contents($inTempPath);
        $inTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $inTemp);
        $inTemp = str_replace('{{field_text}}', $til, $inTemp);
        $addText = str_replace('{{field_def_edi_value}}', '', $inTemp);
        $editText = str_replace('{{field_def_edi_value}}', "{{editData['" . $item['COLUMN_NAME'] . "']}}", $inTemp);
        self::$_page_add_fields_str = empty(self::$_page_add_fields_str) ? $addText : self::$_page_add_fields_str . PHP_EOL . $addText;
        self::$_page_edit_fields_str = empty(self::$_page_edit_fields_str) ? $editText : self::$_page_edit_fields_str . PHP_EOL . $editText;
        $scriptOpsPath = self::$_main_path . DS . 'template' . DS . 'form_ele' . DS . 'script_multi_upload.pholt';
        $scriptTemp = file_get_contents($scriptOpsPath);
        $scriptTemp = str_replace('{{field_name}}', $item['COLUMN_NAME'], $scriptTemp);
        self::$_script_control_initialization = empty(self::$_script_control_initialization)
            ? $scriptTemp : self::$_script_control_initialization . PHP_EOL . $scriptTemp;
    }

    //foreach.body
    private static function foreachBody($item)
    {
        $prefix = self::$_params['relation'] == 1 ? 'a_' : '';
        if (empty(self::$_params['show_fields'])) {
            self::$_foreach_set_data_body = empty(self::$_foreach_set_data_body)
                ? "\$datum['" . $prefix . $item['COLUMN_NAME'] . "_txt'] = empty(\$datum['" . $prefix . $item['COLUMN_NAME'] . "']) ? '' : \$this->_enum_data_" . $item['COLUMN_NAME'] . "[\$datum['" . $prefix . $item['COLUMN_NAME'] . "']];"
                : self::$_foreach_set_data_body . PHP_EOL . "            \$datum['" . $prefix . $item['COLUMN_NAME'] . "_txt'] = empty(\$datum['" . $prefix . $item['COLUMN_NAME'] . "']) ? '' : \$this->_enum_data_" . $item['COLUMN_NAME'] . "[\$datum['" . $prefix . $item['COLUMN_NAME'] . "']];";
        } else {
            $arr = explode(',', self::$_params['show_fields']);
            if (in_array($item['COLUMN_NAME'], $arr)) {
                self::$_foreach_set_data_body = empty(self::$_foreach_set_data_body)
                    ? "\$datum['" . $prefix . $item['COLUMN_NAME'] . "_txt'] = empty(\$datum['" . $prefix . $item['COLUMN_NAME'] . "']) ? '' : \$this->_enum_data_" . $item['COLUMN_NAME'] . "[\$datum['" . $prefix . $item['COLUMN_NAME'] . "']];"
                    : self::$_foreach_set_data_body . PHP_EOL . "            \$datum['" . $prefix . $item['COLUMN_NAME'] . "_txt'] = empty(\$datum['" . $prefix . $item['COLUMN_NAME'] . "']) ? '' : \$this->_enum_data_" . $item['COLUMN_NAME'] . "[\$datum['" . $prefix . $item['COLUMN_NAME'] . "']];";
            }
        }
    }

    //列值专用分析
    private static function listRowValueStr($dataType, $columnName, $enumTxtFlag, $matches)
    {
        if (($dataType == 'bigint' || $dataType == 'int') && Validator::str_end_with($columnName, '_time')) {
            self::$_page_list_data_fields_str = empty(self::$_page_list_data_fields_str) ? "<td>{{date('Y-m-d H:i:s',item['" . $columnName . "'])}}</td>" : self::$_page_list_data_fields_str . PHP_EOL . "                            <td>{{date('Y-m-d H:i:s',item['" . $columnName . "'])}}</td>";
        } elseif (($enumTxtFlag || $dataType == 'enum') && self::$_exclude_cbx === false) {
            self::$_page_list_data_fields_str = empty(self::$_page_list_data_fields_str) ? "<td>{{item['" . $columnName . "_txt']}}</td>" : self::$_page_list_data_fields_str . PHP_EOL . "                            <td>{{item['" . $columnName . "_txt']}}</td>";
        } else {
            self::$_page_list_data_fields_str = empty(self::$_page_list_data_fields_str) ? "<td>{{item['" . $columnName . "']}}</td>" : self::$_page_list_data_fields_str . PHP_EOL . "                            <td>{{item['" . $columnName . "']}}</td>";
        }
    }

    //视图index
    private static function makeViewIndex()
    {
        $vPath = self::$_main_path . DS . 'views'; //视图目录路径
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'view_index.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //replace
        $code = str_replace('{{data_table_comment}}', self::$_data_table_comment, $code);
        $code = str_replace('{{v_path}}', self::$_v_path, $code);
        $code = str_replace('{{m_name}}', self::$_m_name, $code);
        $code = str_replace('{{data_pri_key_comment}}', self::$_data_pri_key_comment, $code);
        //$code = str_replace('{{pri_key_field}}', self::$_data_pri_key_field, $code);
        if (self::$_params['relation'] == 1) {
            $code = str_replace('{{pri_key_field}}', 'a_' . self::$_data_pri_key_field, $code);
        } else {
            $code = str_replace('{{pri_key_field}}', self::$_data_pri_key_field, $code);
        }
        $code = str_replace('{{list_th_title}}', self::$_page_list_til_fields_str, $code);
        $code = str_replace('{{list_td_field_value}}', self::$_page_list_data_fields_str, $code);
        $code = str_replace('{{list_colspan}}', self::$_page_list_colspan_num, $code);
        $code = str_replace('{{script_mod_name}}', self::$_script_mod_name, $code);
        $vPath .= DS . self::$_v_path . self::$_m_name;
        self::mkdir($vPath);
        file_put_contents($vPath . DS . 'index.phtml', $code);
    }

    //视图add
    private static function makeViewAdd()
    {
        $vPath = self::$_main_path . DS . 'views'; //视图目录路径
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'view_add.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //replace
        if (empty(self::$_introducing_third_party_styles)) {
            $code = str_replace('{{introducing_third_party_styles}}', '<!--styles-->', $code);
        } else {
            $code = str_replace('{{introducing_third_party_styles}}', self::$_introducing_third_party_styles, $code);
        }
        if (empty(self::$_introducing_third_party_script)) {
            $code = str_replace('{{introducing_third_party_script}}', '<!--script-->', $code);
        } else {
            $code = str_replace('{{introducing_third_party_script}}', self::$_introducing_third_party_script, $code);
        }
        if (empty(self::$_e_editor_script)) {
            $code = str_replace('{{e_kind_editor_script}}', '//', $code);
        } else {
            self::$_e_editor_script = "    KindEditor.ready(function (K) {" . PHP_EOL . self::$_e_editor_script . PHP_EOL . "    });";
            $code = str_replace('{{e_kind_editor_script}}', self::$_e_editor_script, $code);
        }
        $code = str_replace('{{data_table_comment}}', self::$_data_table_comment, $code);
        $code = str_replace('{{page_add_fields_body_str}}', self::$_page_add_fields_str, $code);
        $code = str_replace('{{script_mod_name}}', self::$_script_mod_name, $code);
        $code = str_replace('{{xm_select_script}}', self::$_xm_select_script, $code);
        if (empty(self::$_add_selected_default_check)) {
            $code = str_replace('{{selected_default_check}}', '//var', $code);
        } else {
            $code = str_replace('{{selected_default_check}}', self::$_add_selected_default_check, $code);
        }
        $vPath .= DS . self::$_v_path . self::$_m_name;
        self::mkdir($vPath);
        file_put_contents($vPath . DS . 'add.phtml', $code);
    }

    //视图edit
    private static function makeViewEdit()
    {
        $vPath = self::$_main_path . DS . 'views'; //视图目录路径
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'view_edit.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //replace
        if (empty(self::$_introducing_third_party_styles)) {
            $code = str_replace('{{introducing_third_party_styles}}', '<!--styles-->', $code);
        } else {
            $code = str_replace('{{introducing_third_party_styles}}', self::$_introducing_third_party_styles, $code);
        }
        if (empty(self::$_introducing_third_party_script)) {
            $code = str_replace('{{introducing_third_party_script}}', '<!--script-->', $code);
        } else {
            $code = str_replace('{{introducing_third_party_script}}', self::$_introducing_third_party_script, $code);
        }
        if (empty(self::$_e_editor_script)) {
            $code = str_replace('{{e_kind_editor_script}}', '//', $code);
        } else {
            //编辑页无需串接，会重复
            //self::$_e_editor_script = "    KindEditor.ready(function (K) {" . PHP_EOL . self::$_e_editor_script . PHP_EOL . "    });";
            $code = str_replace('{{e_kind_editor_script}}', self::$_e_editor_script, $code);
        }
        $code = str_replace('{{data_table_comment}}', self::$_data_table_comment, $code);
        $code = str_replace('{{page_edit_fields_body_str}}', self::$_page_edit_fields_str, $code);
        $code = str_replace('{{script_mod_name}}', self::$_script_mod_name, $code);
        $code = str_replace('{{xm_select_script}}', self::$_xm_select_script, $code);
        if (empty(self::$_edit_selected_default_check)) {
            $code = str_replace('{{selected_default_check}}', '//var', $code);
        } else {
            $code = str_replace('{{selected_default_check}}', self::$_edit_selected_default_check, $code);
        }
        $vPath .= DS . self::$_v_path . self::$_m_name;
        self::mkdir($vPath);
        file_put_contents($vPath . DS . 'edit.phtml', $code);
    }

    //JS脚本
    private static function makeScript()
    {
        $vPath = BASE_PATH . DS . 'public' . DS . 'static' . DS . 'modules'; //脚本目录路径
        //代码模板路径
        $codeTemplate = self::$_main_path . DS . 'template' . DS . 'script_module.pholt';
        //code
        $code = file_get_contents($codeTemplate);
        //replace
        $code = str_replace('{{v_path}}', self::$_v_path, $code);
        $code = str_replace('{{m_name}}', self::$_m_name, $code);
        if (empty(self::$_script_control_initialization)) {
            $code = str_replace('{{public_control_initialization}}', '            //init', $code);
        } else {
            $code = str_replace('{{public_control_initialization}}', self::$_script_control_initialization, $code);
        }
        $code = str_replace('{{script_mod_name}}', self::$_script_mod_name, $code);
        file_put_contents($vPath . DS . self::$_script_mod_name . '.js', $code);
    }

    //menu
    private static function createMgrMenu()
    {
        if (self::$_params['m_type'] == 2) {
            $fData2 = [];
            $fData3 = [];
            if (empty(self::$_params['custom_dArr'])) {
                $fData = [
                    'parent_id' => 0, 'rule_path' => self::$_m_name,
                    'title' => (empty(self::$_data_table_comment) ? self::$_m_name : self::$_data_table_comment),
                    'icon' => '&#xe6b4;', 'is_menu' => 1, 'create_time' => time(), 'subordinate' => 1
                ];
            } else {
                $fData = [
                    'parent_id' => 0, 'rule_path' => strtolower(self::$_params['custom_dArr'][0]),
                    'title' => strtolower(self::$_params['custom_dArr'][0]), 'icon' => '&#xe6b4;',
                    'is_menu' => 1, 'create_time' => time(), 'subordinate' => 1
                ];
                if (count(self::$_params['custom_dArr']) > 1) {
                    $fData2 = [
                        'parent_id' => 0, 'rule_path' => strtolower(self::$_params['custom_dArr'][1]),
                        'title' => strtolower(self::$_params['custom_dArr'][1]),
                        'icon' => '&#xe83c;', 'is_menu' => 1, 'create_time' => time(), 'subordinate' => 1
                    ];
                    $fData3 = [
                        'parent_id' => 0, 'rule_path' => strtolower(self::$_params['custom_dArr'][0]) . '/' . strtolower(self::$_params['custom_dArr'][1]) . '/' . self::$_m_name,
                        'title' => (empty(self::$_data_table_comment) ? self::$_m_name : self::$_data_table_comment),
                        'is_menu' => 1, 'create_time' => time(), 'subordinate' => 1
                    ];
                } else {
                    $fData2 = [
                        'parent_id' => 0, 'rule_path' => strtolower(self::$_params['custom_dArr'][0]) . '/' . self::$_m_name,
                        'title' => (empty(self::$_data_table_comment) ? self::$_m_name : self::$_data_table_comment),
                        'is_menu' => 1, 'create_time' => time(), 'subordinate' => 1
                    ];
                }
            }
            $fc = MenuRule::findFirst("rule_path='" . $fData['rule_path'] . "'");
            if ($fc) {
                $parent_id = $fc->id;
                if (!empty($fData2)) {
                    $fc2 = MenuRule::findFirst("rule_path='" . $fData2['rule_path'] . "'");
                    if ($fc2) {
                        if (!empty($fData3)) {
                            $fc3 = MenuRule::findFirst("rule_path='" . $fData3['rule_path'] . "'");
                            if ($fc3) {
                                $parent_id = $fc3->id;
                            } else {
                                $fData3['parent_id'] = $fc2->id;
                                $m3 = new MenuRule();
                                $m3->assign($fData3)->create();
                                $parent_id = $m3->getLastInsertId();
                            }
                            self::createMenuRule($parent_id, $fData3, true);
                        } else {
                            $parent_id = $fc2->id;
                            self::createMenuRule($parent_id, $fData2, true);
                        }
                    } else {
                        $fData2['parent_id'] = $parent_id;
                        $m2 = new MenuRule();
                        $m2->assign($fData2)->create();
                        $parent_id = $m2->getLastInsertId();
                        if (!empty($fData3)) {
                            $fc3 = MenuRule::findFirst("rule_path='" . $fData3['rule_path'] . "'");
                            if ($fc3) {
                                $parent_id = $fc3->id;
                            } else {
                                $fData3['parent_id'] = $fc2->id;
                                $m3 = new MenuRule();
                                $m3->assign($fData3)->create();
                                $parent_id = $m3->getLastInsertId();
                            }
                            self::createMenuRule($parent_id, $fData3, true);
                        } else {
                            self::createMenuRule($parent_id, $fData2, true);
                        }
                    }
                } else {
                    self::createMenuRule($parent_id, $fData, true);
                }
            } else {
                $m = new MenuRule();
                $m->assign($fData)->create();
                $parent_id = $m->getLastInsertId();
                if (!empty($fData2)) {
                    $fc2 = MenuRule::findFirst("rule_path='" . $fData2['rule_path'] . "'");
                    if ($fc2) {
                        if (!empty($fData3)) {
                            $fc3 = MenuRule::findFirst("rule_path='" . $fData3['rule_path'] . "'");
                            if ($fc3) {
                                $parent_id = $fc3->id;
                            } else {
                                $fData3['parent_id'] = $fc2->id;
                                $m3 = new MenuRule();
                                $m3->assign($fData3)->create();
                                $parent_id = $m3->getLastInsertId();
                            }
                            self::createMenuRule($parent_id, $fData3, true);
                        } else {
                            $parent_id = $fc2->id;
                            self::createMenuRule($parent_id, $fData2, true);
                        }
                    } else {
                        $fData2['parent_id'] = $parent_id;
                        $m2 = new MenuRule();
                        $m2->assign($fData2)->create();
                        $parent_id = $m2->getLastInsertId();
                        if (!empty($fData3)) {
                            $fc3 = MenuRule::findFirst("rule_path='" . $fData3['rule_path'] . "'");
                            if ($fc3) {
                                $parent_id = $fc3->id;
                            } else {
                                $fData3['parent_id'] = $parent_id;
                                $m3 = new MenuRule();
                                $m3->assign($fData3)->create();
                                $parent_id = $m3->getLastInsertId();
                            }
                            self::createMenuRule($parent_id, $fData3, true);
                        } else {
                            self::createMenuRule($parent_id, $fData2);
                        }
                    }
                } else {
                    self::createMenuRule($parent_id, $fData);
                }
            }
        }
    }

    //create.menu.rule
    private static function createMenuRule($parent_id, $fData, $chk = false)
    {
        $cData = [
            [
                'parent_id' => $parent_id, 'rule_path' => $fData['rule_path'] . '/index', 'title' => '查看',
                'is_menu' => 0, 'create_time' => time(), 'subordinate' => 0
            ],
            [
                'parent_id' => $parent_id, 'rule_path' => $fData['rule_path'] . '/add', 'title' => '添加',
                'is_menu' => 0, 'create_time' => time(), 'subordinate' => 0
            ],
            [
                'parent_id' => $parent_id, 'rule_path' => $fData['rule_path'] . '/edit', 'title' => '编辑',
                'is_menu' => 0, 'create_time' => time(), 'subordinate' => 0
            ],
            [
                'parent_id' => $parent_id, 'rule_path' => $fData['rule_path'] . '/del', 'title' => '删除',
                'is_menu' => 0, 'create_time' => time(), 'subordinate' => 0
            ]
        ];
        foreach ($cData as $datum) {
            if ($chk) {
                $ck = MenuRule::findFirst("rule_path='" . $datum['rule_path'] . "'");
                if (empty($ck)) {
                    (new MenuRule())->assign($datum)->create();
                }
            } else {
                (new MenuRule())->assign($datum)->create();
            }
        }
    }

    //单独处理关联未关联的情况
    private static function relationHandle($code)
    {
        if (self::$_params['relation'] == 1) {
            if (in_array('is_del', self::$_all_fields_arr)) {
                $code = str_replace('{{def_where}}', "'a.is_del=0'", $code);
            } else {
                $code = str_replace('{{def_where}}', 'null', $code);
            }
            $code = str_replace('{{list_func}}', 'joinList', $code);
            if (empty(self::$_params['show_fields'])) {
                $fields = 'a.' . str_replace(',', ',a.', implode(',', self::$_data_list_fields));
            } else {
                $fields = 'a.' . str_replace(',', ',a.', self::$_params['show_fields']);
            }
            $fields .= self::readFieldsAnalysis();
            $code = str_replace('{{read_fields}}', $fields, $code);
            $code = str_replace('{{alias}}', 'a', $code);
            //JOIN
            $joinStr = PHP_EOL;
            foreach (self::$_params['main_table'] as $key => $item_table) {
                $m = Tools::camelize(
                    str_replace(self::$_db_config->database->prefix, '', $item_table),
                    '_',
                    true
                );
                $mStr = "                ['model' => " . $m . "::class, 'conditions' => 'a." . self::$_params['main_table_foreign_key'][$key] . "=" . self::$_alias[$key] . "." . self::$_params['relation_primary_key'][$key] . "', 'alias' => '" . self::$_alias[$key] . "']," . PHP_EOL;
                $joinStr .= $mStr;
            }
            $joinStr .= "            ";
            $code = str_replace('{{join}}', $joinStr, $code);
            $code = str_replace('{{pri_key_field}}', 'a.' . self::$_data_pri_key_field, $code);
        } else {
            if (in_array('is_del', self::$_all_fields_arr)) {
                $code = str_replace('{{def_where}}', "'is_del=0'", $code);
            } else {
                $code = str_replace('{{def_where}}', 'null', $code);
            }
            $code = str_replace('{{list_func}}', 'defList', $code);
            if (empty(self::$_params['show_fields'])) {
                $code = str_replace('{{read_fields}}', implode(',', self::$_data_list_fields), $code);
            } else {
                $code = str_replace('{{read_fields}}', self::$_params['show_fields'], $code);
            }
            $code = str_replace('{{alias}}', '', $code);
            $code = str_replace('{{join}}', '', $code);
            $code = str_replace('{{pri_key_field}}', self::$_data_pri_key_field, $code);
        }
        return $code;
    }

    //删除的处理
    private static function addEditDelHandle($code)
    {
        if (in_array('is_del', self::$_all_fields_arr)) {
            $code = str_replace('{{soft_deletion}}', '', $code);
            $code = str_replace('{{hard_delete}}', '//', $code);
        } else {
            $code = str_replace('{{soft_deletion}}', '//', $code);
            $code = str_replace('{{hard_delete}}', '', $code);
        }
        return $code;
    }

    //下拉提供处理
    private static function dropDownHandle($code)
    {
        $conditions = "";
        $conditions2 = "";
        $isStr = false;
        if (in_array('status', self::$_all_fields_arr)) {
            foreach (self::$_data_table_fields as $item) {
                if ($item['COLUMN_NAME'] == 'status') {
                    if (stripos($item['COLUMN_TYPE'], 'normal') !== false) {
                        $conditions = "status='normal'";
                        $conditions2 = "a.status='normal'";
                        $isStr = true;
                    }
                    break;
                }
            }
            if (empty($conditions)) {
                $conditions = 'status=1';
            }
            if (empty($conditions2)) {
                $conditions2 = 'a.status=1';
            }
        }
        $rep = "";
        $rep2 = 'null';
        if (in_array('is_del', self::$_all_fields_arr)) {
            if (empty($conditions)) {
                $rep = "'conditions' => 'is_del=0', ";
            } else {
                if ($isStr) {
                    $rep = "'conditions' => \"" . $conditions . " AND is_del=0\", ";
                } else {
                    $rep = "'conditions' => '" . $conditions . " AND is_del=0', ";
                }
            }
            if (empty($conditions2)) {
                $rep2 = "'a.is_del=0'";
            } else {
                if ($isStr) {
                    $rep2 = "\"" . $conditions2 . " AND a.is_del=0\"";
                } else {
                    $rep2 = "'" . $conditions2 . " AND a.is_del=0'";
                }
            }
        } else {
            if (!empty($conditions)) {
                if ($isStr) {
                    $rep = "'conditions' => \"" . $conditions . "\", ";
                } else {
                    $rep = "'conditions' => '" . $conditions . "', ";
                }
            }
            if (!empty($conditions2)) {
                if ($isStr) {
                    $rep2 = "\"" . $conditions2 . "\"";
                } else {
                    $rep2 = "'" . $conditions2 . "'";
                }
            }
        }
        $rep .= "'columns' => '" . implode(',', self::$_sel_ops_fields) . "'";
        $code = str_replace('{{ops_parameters}}', $rep, $code);
        $code = str_replace('{{ops_xms_where}}', $rep2, $code);
        $code = str_replace('{{data_selection_name_field}}', self::$_sel_ops_fields[1], $code);
        $code = str_replace('{{data_pri_key_field}}', self::$_sel_ops_fields[0], $code);
        return str_replace('{{parent_id_field_name}}', self::$_parent_id_field_name, $code);
    }

    //要读取的关联表的字段处理
    private static function readFieldsAnalysis(): string
    {
        $fields = '';
        //子表的字段
        foreach (self::$_params['relation_fields'] as $key => $item_field) {
            if (!empty($item_field)) {
                $j_fields = self::$_alias[$key] . '.' . str_replace(',', ',' . self::$_alias[$key] . '.', $item_field);
                $fields .= ',' . $j_fields;
            }
        }
        return $fields;
    }

    //基本数据
    private static function baseData()
    {
        //读取主数据表字段（包括类型的数据集）
        self::getTableFields(self::$_params['data_table'], self::$_data_table_fields);
        //主数据字段名数组（仅字段名的数组）
        self::$_all_fields_arr = array_column(self::$_data_table_fields, 'COLUMN_NAME');
        //列表展示字段（移除默认的is_del）
        self::$_data_list_fields = array_filter(self::$_all_fields_arr, function ($value) {
            return $value != 'is_del';
        });
        //主表注释
        self::getTableComment(self::$_params['data_table']);
        //后台模块主目录路径
        self::$_main_path = dirname(__DIR__);
        //db.config
        self::$_db_config = Di::getDefault()->getShared('database');
        //统一的名称，控制器、模型、验证，转驼峰
        self::$_unify_name = Tools::camelize(
            str_replace(self::$_db_config->database->prefix, '', self::$_params['data_table']),
            '_',
            true
        );
        //取主键字段
        foreach (self::$_data_table_fields as $item) {
            if ($item['COLUMN_KEY'] == 'PRI' && $item['EXTRA'] == 'auto_increment') {
                self::$_data_pri_key_field = $item['COLUMN_NAME'];
                self::$_data_pri_key_comment = !empty($item['COLUMN_COMMENT'])
                    ? $item['COLUMN_COMMENT'] : self::$_data_pri_key_comment;
                break;
            }
        }
        //查找一个作为下拉选择的名称的字段
        self::findSelectionNameField();
        self::$_sel_ops_fields[] = self::$_data_pri_key_field;
        self::$_sel_ops_fields[] = self::$_data_selection_name_field;
        //视图目录路径，views下的部分
        self::$_v_path = !empty(self::$_params['custom_dArr']) ? strtolower(self::$_params['custom_dir']) . '/' : '';
        //视图目录路径中对应数据表的名称
        self::$_m_name = strtolower(str_replace(self::$_db_config->database->prefix, '', self::$_params['data_table']));
        //script.name
        if (!empty(self::$_v_path)) {
            $nv = rtrim(self::$_v_path, '/');
            $nv = str_replace('/', '_', $nv);
            $nv .= '_' . self::$_m_name;
        } else {
            $nv = self::$_m_name;
        }
        self::$_script_mod_name = $nv;
    }

    #region 暂定锁代码块

    //创建名称空间配置
    private static function makeNamespaceConfig()
    {
        $fPath = self::$_main_path . DS . 'config' . DS . 'namespaces.php';
        self::$_ctrl_name_space = 'Pha\Modules\Backend\Controllers\\' . ucfirst(strtolower(self::$_params['custom_dArr'][0]));
        if (isset(self::$_params['custom_dArr'][1])) {
            self::$_ctrl_name_space .= '\\' . ucfirst(strtolower(self::$_params['custom_dArr'][1]));
        }
        $np = "dirname(__DIR__) . '/controllers/" . strtolower(self::$_params['custom_dir']) . '/';
        $write_line = "    '" . self::$_ctrl_name_space . "' => " . $np . "',";
        $configText = @file_get_contents($fPath);
        if (strpos($configText, self::$_ctrl_name_space) === false) {
            $configText = str_replace('];', '', $configText);
            $configText .= $write_line . PHP_EOL . '];';
            @file_put_contents($fPath, $configText);
        }
    }

    //查找一个作为下拉选择的名称的字段
    private static function findSelectionNameField()
    {
        foreach (self::$_data_table_fields as $item) {
            $sName = strtolower($item['COLUMN_NAME']);
            if ($sName == 'name' || $sName == 'title'
                || stripos($sName, '_name') !== false || stripos($sName, '_title') !== false) {
                self::$_data_selection_name_field = $item['COLUMN_NAME'];
                break;
            }
        }
        if (empty(self::$_data_selection_name_field)) {
            foreach (self::$_data_table_fields as $item) {
                if ($item['DATA_TYPE'] == 'varchar') {
                    self::$_data_selection_name_field = $item['COLUMN_NAME'];
                    break;
                }
            }
        }
        if (empty(self::$_data_selection_name_field)) {
            self::$_data_selection_name_field = self::$_data_pri_key_field;
        }
    }

    //获取表字段
    private static function getTableFields($table, &$var)
    {
        $dbConfig = Di::getDefault()->getShared('database');
        $db = Di::getDefault()->getShared('db');
        $sql = "SELECT COLUMN_NAME,COLUMN_DEFAULT,IS_NULLABLE,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,"
            . "NUMERIC_PRECISION,COLUMN_TYPE,COLUMN_KEY,EXTRA,COLUMN_COMMENT "
            . "FROM information_schema.`COLUMNS` "
            . "WHERE TABLE_SCHEMA = '" . $dbConfig->database->dbname . "' AND TABLE_NAME = '" . $table . "' "
            . "ORDER BY ORDINAL_POSITION ASC";
        $data = $db->query($sql);
        //筛选只要字符键名的数据，不要数字下标的数据
        $data->setFetchMode(Enum::FETCH_ASSOC);
        $var = $data->fetchAll();
    }

    //获取表注释
    private static function getTableComment($table)
    {
        $db = Di::getDefault()->getShared('db');
        $sql = "SELECT TABLE_COMMENT FROM information_schema.`TABLES` WHERE table_name='" . $table . "'";
        $data = $db->query($sql);
        //筛选只要字符键名的数据，不要数字下标的数据
        $data->setFetchMode(Enum::FETCH_ASSOC);
        $list = $data->fetchAll();
        self::$_data_table_comment = $list[0]['TABLE_COMMENT'];
        self::$_data_table_comment = str_replace('表', '', self::$_data_table_comment);
    }

    //创建目录
    private static function mkdir($dir, $pms = 0777): void
    {
        if (is_dir($dir) || is_file($dir)) {
            return;
        }
        self::mkdir(dirname($dir), $pms);
        mkdir($dir, $pms, true);
    }

    #endregion

}
