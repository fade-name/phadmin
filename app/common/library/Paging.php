<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Library;

/**
 * 分页类
 */
class Paging
{

    protected $pg_url;            //URL
    protected $pg_total;          //总记录数
    protected $pg_size;           //每页显示的记录数
    protected $pg_page;           //当前页
    //总页数
    protected $pg_page_count;
    //起头页数
    protected $pg_start;
    //结尾页数
    protected $pg_end;

    /**
     * 构造函数
     * @param string $pg_url 分页的页面地址URL
     * @param int $pg_total 总记录数
     * @param int $pg_size 每页显示的记录数
     * @param int $pg_page 当前页
     * @param int $show_pages 控制页面显示链接页数，显示链接的页数为2*$show_pages+1
     */
    public function __construct(string $pg_url, int $pg_total = 1, int $pg_size = 1, int $pg_page = 1, int $show_pages = 2)
    {
        $this->pg_url = $pg_url;
        $this->pg_total = $this->numeric($pg_total);
        $this->pg_size = $this->numeric($pg_size);
        $this->pg_page = $this->numeric($pg_page);
        if ($this->pg_size <= 0) {
            $this->pg_size = 1;
        }
        $this->pg_page_count = ceil($this->pg_total / $this->pg_size);

        if ($this->pg_total < 0) {
            $this->pg_total = 0;
        }
        if ($this->pg_page < 1) {
            $this->pg_page = 1;
        }
        if ($this->pg_page_count < 1) {
            $this->pg_page_count = 1;
        }
        if ($this->pg_page > $this->pg_page_count) {
            $this->pg_page = $this->pg_page_count;
        }
        $this->pg_start = $this->pg_page - $show_pages;
        $this->pg_end = $this->pg_page + $show_pages;
        if ($this->pg_start < 1) {
            $this->pg_end = $this->pg_end + (1 - $this->pg_start);
            $this->pg_start = 1;
        }
        if ($this->pg_end > $this->pg_page_count) {
            $this->pg_start = $this->pg_start - ($this->pg_end - $this->pg_page_count);
            $this->pg_end = $this->pg_page_count;
        }
        if ($this->pg_start < 1) {
            $this->pg_start = 1;
        }
    }

    /**
     * 检测是否为数字
     */
    protected function numeric($num)
    {
        if (strlen($num)) {
            if (!preg_match("/^[0-9]+$/", $num)) {
                $num = 1;
            } else {
                $num = substr($num, 0, 9);
            }
        } else {
            $num = 1;
        }
        return $num;
    }

    /**
     * 地址替换
     */
    protected function page_replace($page)
    {
        return str_replace('{P}', $page, $this->pg_url);
    }

    /**
     * 首页
     */
    protected function pg_home()
    {
        //class:laypage_first
        if ($this->pg_page != 1) {
            return '<a href="' . $this->page_replace(1) . '" class="first" data-page="1">首页</a>';
        } else {
            return '';
        }
    }

    /**
     * 上一页
     */
    protected function pg_prev()
    {
        //class:layui-laypage-prev
        if ($this->pg_page != 1) {
            return '<a href="' . $this->page_replace($this->pg_page - 1) . '" class="prev" data-page="' . ($this->pg_page - 1) . '">上一页</a>';
        } else {
            return '';
        }
    }

    /**
     * 下一页
     */
    protected function pg_next()
    {
        //class:layui-laypage-next
        if ($this->pg_page != $this->pg_page_count) {
            return '<a href="' . $this->page_replace($this->pg_page + 1) . '" class="next" data-page="' . ($this->pg_page + 1) . '">下一页</a>';
        } else {
            return '';
        }
    }

    /**
     * 尾页
     */
    protected function pg_last()
    {
        //class:layui-laypage-last
        if ($this->pg_page != $this->pg_page_count) {
            return '<a href="' . $this->page_replace($this->pg_page_count) . '" class="last" data-page="' . $this->pg_page_count . '">尾页</a>';
        } else {
            return '';
        }
    }

    /**
     * 输出
     */
    public function pg_write()
    {
        // class="layui-box layui-laypage layui-laypage-sys" id="layui-laypage-5"
        $str = '<div>';
        $str .= $this->pg_home();
        $str .= $this->pg_prev();
        if ($this->pg_start > 1) {
            $str .= '<span>...</span>';
        }
        for ($i = $this->pg_start; $i <= $this->pg_end; $i++) {
            if ($i == $this->pg_page) {
                $str .= '<span class="current">' . $i . '</span>';
            } else {
                $str .= '<a class="num" href="' . $this->page_replace($i) . '" data-page="' . $i . '">' . $i . '</a>';
            }
        }
        if ($this->pg_end < $this->pg_page_count) {
            $str .= '<span>...</span>';
        }
        $str .= $this->pg_next();
        $str .= $this->pg_last();
        //$str .= '<span class="layui-laypage-skip">到第';
        //$str .= '<input type="text" min="1" max="' . $this->pg_page_count . '" url="' . $this->pg_url . '" value="' . $this->pg_page . '" class="layui-input for_input_submit_goto">';
        //$str .= '页<button type="button" class="layui-laypage-btn for_input_submit_btn">确定</button></span>';
        $str .= '&nbsp;<span>共<b>' . $this->pg_page_count . '</b>页，<b>' . $this->pg_total . '</b>条数据</span>';
        $str .= '</div>';
        return $str;
    }

}
