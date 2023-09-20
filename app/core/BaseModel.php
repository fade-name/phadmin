<?php

// +----------------------------------------------------------------------
// | Copyright: 发德名工作室，保留所有权利
// +----------------------------------------------------------------------
// | Author: 发德名 <2295943610@qq.com>
// +----------------------------------------------------------------------
// | Explain: 遵循 Apache 2.0 开源协议，欢迎使用
// +----------------------------------------------------------------------

namespace Pha\Core;

use Pha\Library\Paging;
use Phalcon\Db\Adapter\AdapterInterface;
use Phalcon\Di;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Mvc\Model\Transaction\Manager;
use Redis;

class BaseModel extends \Phalcon\Mvc\Model implements \Phalcon\Mvc\ModelInterface
{

    /**
     * @var Di
     */
    protected $_di;
    /**
     * @var AdapterInterface
     */
    protected $_db;
    /**
     * @var Redis
     */
    protected $_redis;
    /**
     * Table Prefix
     */
    protected $_prefix;
    /**
     * Error
     */
    public $error = '';

    public function initialize()
    {
        $this->_di = $this->getDI();
        $this->_db = $this->_di->getShared('db');
        $this->_redis = $this->_di->getShared('redis');
        $database = $this->_di->getShared('database');
        $this->_prefix = $database->database->prefix;
    }

    protected function setErrReturn($err = ''): bool
    {
        $this->error = $err;
        return false;
    }

    /**
     * 获取插入数据的ID
     */
    public function getLastInsertId()
    {
        return $this->getWriteConnection()->lastInsertId($this->getSource());
    }

    /**
     * 创建Builder
     */
    public function cb(): BuilderInterface
    {
        return $this->getModelsManager()->createBuilder();
    }

    public function getTransaction()
    {
        return (new Manager())->get();
    }

    public function emptyCount($count, $pager): array
    {
        if (empty($pager)) {
            $pager = ['page' => 1, 'limit' => 20, 'offset' => 0];
        }
        return [
            'list' => [], 'count' => $count, 'curPage' => $pager['page'],
            'pageSize' => $pager['limit'], 'pageCount' => 0
        ];
    }

    //默认仅支持left join
    public function countNum(BuilderInterface $builder, $id_keyName, $class, $alias = '', $where = null, $join = [])
    {
        $builder->columns("count({$id_keyName}) as count");
        if (!empty($alias)) {
            $builder->from([$alias => $class]);
        } else {
            $builder->from($class);
        }
        if (!empty($join)) {
            foreach ($join as $item) {
                $builder->leftJoin($item['model'], $item['conditions'], $item['alias']);
            }
        }
        if ($where !== null) {
            $builder->where($where);
        }
        return $builder->getQuery()->execute()[0]->count;
    }

    public function hasCount(BuilderInterface $builder, $count, $pager, $order, $field, $where = null): array
    {
        $builder->columns($field);
        if ($where !== null) {
            $builder->where($where);
        }
        $builder->orderBy($order);
        if (!empty($pager)) {
            $builder->limit($pager['limit'], $pager['offset']);
        } else {
            $pager = ['page' => 1, 'limit' => 20, 'offset' => 0];
        }
        $list = $builder->getQuery()->execute()->toArray();
        $pageCount = ceil($count / $pager['limit']); //总页数
        return [
            'list' => $list, 'count' => $count, 'curPage' => $pager['page'],
            'pageSize' => $pager['limit'], 'pageCount' => $pageCount
        ];
    }

    //无join时用
    public function defList($where, $class, $fields, $pager, $pageLink, $alias = '', $join = [],
                            $idKeyName = 'id', $orderBy = 'id DESC'): array
    {
        $builder = $this->cb();

        $count = $this->countNum($builder, $idKeyName, $class, $alias, $where);
        $result = $count <= 0
            ? $this->emptyCount($count, $pager)
            : $this->hasCount($builder, $count, $pager, $orderBy, $fields, $where);

        if (!empty($pager) && !empty($pageLink)) {
            $paging = new Paging($pageLink, $count, $pager['limit'], $pager['page']);
            $result['pageLink'] = $paging->pg_write();
        } else {
            $result['pageLink'] = '';
        }

        return $result;
    }

    //有join时用，默认仅支持left join
    //注意：$showPageLink，这个是WEB页面有分页链接才用展示的，一般接口此参数需传入false
    public function joinList($where, $class, $fields, $pager, $pageLink, $alias = '', $join = [],
                             $idKeyName = 'id', $orderBy = 'id DESC', $pagination = true,
                             $showPageLink = true): array
    {
        $data = $this->emptyCount(0, $pager);
        if ($showPageLink) {
            $data['pageLink'] = $pageLink;
        }
        $builder = $this->cb();
        $count = $this->countNum($builder, $idKeyName, $class, $alias, $where, $join);
        if ($count <= 0) {
            if ($showPageLink) {
                $data['pageLink'] = '';
            }
            return $data;
        }
        $builder->columns($fields);
        if (!empty($orderBy)) {
            $builder->orderBy($orderBy);
        }
        if ($pagination) {
            $builder->limit($pager['limit'], $pager['offset']);
        }
        $data['list'] = $builder->getQuery()->execute()->toArray();
        $data['count'] = $count;
        $data['pageCount'] = (ceil($count / $pager['limit']));
        if ($showPageLink) {
            $paging = new Paging($data['pageLink'], $count, $pager['limit'], $pager['page'], 7);
            $data['pageLink'] = $paging->pg_write();
        }
        return $data;
    }

}
