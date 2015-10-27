<?php
/**
 * API Cloud query build
 * @author wangxianlong <xianlong300@sian.com>
 */
namespace app\utils\apicloud;

class QueryBuilder
{

    /**
     * 查询条件数组
     * @var array
     */
    private $where;

    /**
     * 数据表
     * @var string
     */
    private $from;

    /**
     * 查询字段数组
     * @var array
     */
    private $select;

    /**
     * 限制分页数组
     * @var array
     */
    private $limit;

    /**
     * 获取查询表
     * @return string 查询表
     */
    public function getForm()
    {
        return $this->from;
    }

    /**
     * 设置查询表
     * @param  string $tables 表名称
     * @return object         queryBuild对象
     */
    public function from($tables)
    {

        $this->from = trim($tables);
        return $this;
    }

    /**
     * 设置查询表
     * @param  array|string $columns 返回的列名称数据
     * @return object         queryBuild对象
     */
    public function select($columns)
    {
        if (!is_array($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->select = $columns;
        return $this;
    }

    /**
     * 查询条件设置
     * @param  array $condition 查询条件数组
     * @return object         queryBuild对象
     */
    public function where($condition)
    {
        $this->where = $condition;
        return $this;
    }

    /**
     * 添加与条件
     * @param  array $condition 查询条件数组
     * @return object         queryBuild对象
     */
    public function andWhere($condition)
    {
        if ($this->where === null) {
            $this->where = $condition;
        } else {
            $this->where = ['and', $this->where, $condition];
        }

        return $this;
    }

    /**
     * 添加或条件
     * @param  array $condition 查询条件数组
     * @return object         queryBuild对象
     */
    public function orWhere($condition)
    {
        if ($this->where === null) {
            $this->where = $condition;
        } else {
            $this->where = ['or', $this->where, $condition];
        }

        return $this;
    }

    /**
     * 限制分页数组
     * $data[0]:分页跳过数目，$data[1]分页大小
     * 如果只有$data[0]，限制返回数目
     * @param  array $condition 分页数组
     * @return object         queryBuild对象
     */
    public function limit($data)
    {
        if(is_array($data))
        {
            $this->limit = [$data[0],$data[1]];
        }
        else
            $this->limit = $data;
        return $this;
    }

    /**
     * 排序设置
     * ```php
     * $orders = ['id DESC','name asc'];
     * ```
     * @param  [type] $orders [description]
     *
     * @return [type]         [description]
     */
    public function orderBy( $orders )
    {
        if( is_string($orders) )
        {
            $orders = explode(',', $orders);
        }
        foreach ($orders as $key => $item)
        {
            $this->order[] = $item;
        }
        return $this;
    }

    /**
     * 生成查询数组
     * @return array 查询数组
     */
    public function build()
    {
        $filter = [];
        if( $this->select )
        {
            if( is_string( $this->select ) )
            {
                $this->select = explode(',', $this->select);
            }

            foreach ($this->select as  $item)
            {
                $filter['fields'][$item] = true;
            }
        }
        if( $this->limit )
        {
            if(is_array($this->limit))
            {
                $filter['limit'] = intval($this->limit[1]);
                $filter['skip'] = intval($this->limit[0]);
            }
            else
            {
                $filter['limit'] = intval($this->limit);
            }
        }

        if( $this->orders )
        {
            foreach ($this->orders as $item)
            {
                $filter['order'][] = $item;
            }
            if( count( $filter['order'] ) == 1 )
            {
                $filter['order'] = $filter['order'][0];
            }
        }

        if($this->where)
        {
            $filter['where'] = $this->getWhere($this->where);

        }
        return $filter;
    }

    /**
     * 递归生成查询条件
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    private function getWhere($where)
    {
        $condition = [];


        if( array_keys($where) === range(0, count($where) - 1) && count($where) == 3 )
        {
            if( $where[0] == 'and' || $where[0] == 'or' )
            {
                $condition[$where[0]][] = $this->getWhere($where[1]);
                $condition[$where[0]][] = $this->getWhere($where[2]);
            }
            elseif( $where[0] == 'between' )
            {
                $condition[$where[1]]['between'] = $where[2];
            }
            elseif( $where[0] == '>')
            {
                $condition[$where[1]]['gt'] = $where[2];
            }
            elseif( $where[0] == '<')
            {
                $condition[$where[1]]['lt'] = $where[2];
            }
            elseif( $where[0] == '>=')
            {
                $condition[$where[1]]['gte'] = $where[2];
            }
            elseif( $where[0] == '=<')
            {
                $condition[$where[1]]['lte'] = $where[2];
            }
            elseif( $where[0] == 'in' )
            {
                $condition[$where[1]]['inq'] = $where[2];
            }
            elseif( $where[0] == 'not in' )
            {
                $condition[$where[1]]['nin'] = $where[2];
            }
            elseif( $where[0] == '!=' )
            {
                $condition[$where[1]]['ne'] = $where[2];
            }
            elseif( $where[0] == 'like' )
            {
                $condition[$where[1]]['like'] = $where[2];
            }
        }
        elseif(array_keys($where) !== range(0, count($where) - 1))
        {

            $condition = $where;
        }
        return $condition;
    }

}
