<?php
namespace Libs\DB;
/**
 * 简易SQL生成器
 */
class QueryMaker
{

    const PREPARE_PREFIX = ':prepared_';

    private $tableName;
    private $operation = 'SELECT';
    private $field = array();
    private $data = array();
    private $where;
    private $orderBy;
    private $groupBy;
    private $limit = 'LIMIT 2000';
    //替换后的映射表
    private $prepared = array();
    private $sql = '';
    private $customField = FALSE;

    /**
     * 实例
     * @return QueryMaker
     */
    public static function newInstance()
    {
        return new QueryMaker();
    }

    /**
     * 获取select
     * @return QueryMaker
     */
    public function select()
    {
        $this->operation = 'SELECT';
        return $this;
    }

    /**
     * 获取count
     * @return QueryMaker
     */
    public function count()
    {
        $this->operation = 'COUNT';
        return $this;
    }

    /**
     * 插入条记录
     * @return QueryMaker
     */
    public function insert()
    {
        $this->operation = 'INSERT';
        return $this;
    }

    /**
     * replace into
     * @return QueryMaker
     */
    public function replace()
    {
        $this->operation = 'REPLACE';
        return $this;
    }

    /**
     * 更新
     * @return QueryMaker
     */
    public function update()
    {
        $this->operation = 'UPDATE';
        return $this;
    }

    /**
     * 删除
     * @return QueryMaker
     */
    public function delete()
    {
        $this->operation = 'DELETE';
        return $this;
    }

    /**
     * 设置表名
     * @param string $tableName
     * @return QueryMaker
     */
    public function setTableName($tableName)
    {
        if ($tableName) {
            $this->tableName = $this->_prepareFieldName($tableName);
        }
        return $this;
    }

    /**
     * 设置字段
     * @param Array /String $field
     * @param Bool $custom 是否为自定义字段 ext 重合名 禁止替换空格
     * @return QueryMaker
     */
    public function setField($field, $custom = FALSE)
    {
        if ($field) {
            if (!is_array($field)) {
                $field = explode(',', $field);
            }
            $this->field = $field;
        }
        $this->customField = $custom;
        return $this;
    }


    /**
     * 设置where条件
     * @param Array /String $where
     * @return QueryMaker
     */
    public function setWhere($where)
    {
        if (is_array($where)) {
            $data = array();
            foreach ($where as $key => $val) {
                if (is_array($val)) {
                    $tmp = array();
                    foreach ($val as $row) {
                        $tmp[] = $this->_prepareData($row);
                    }
                    $data[] = $this->_prepareFieldName($key) . ' IN (' . implode(',', $tmp) . ')';
                } else {
                    $data[] = $this->_prepareFieldName($key) . " = " . $this->_prepareData($val);
                }
            }
            $this->where = 'WHERE ' . implode(' AND ', $data);
        } elseif (is_string($where)) {
            $this->where = "WHERE $where";
        }
        return $this;
    }

    /**
     * 设置排序
     * @param Array $orderBy = array('id' => 1); value 是 true 表示倒序，反之是正序
     * @return QueryMaker
     */
    public function setOrderBy(Array $orderBy)
    {
        if ($orderBy) {
            $tmp = array();
            foreach ($orderBy as $key => $val) {
                if (is_numeric($key)) {
                    continue;
                }
                $order = $val ? 'DESC' : 'ASC';
                $tmp[] = $this->_prepareFieldName($key) . ' ' . $order;
            }
            $tmp = implode(',', $tmp);
            $this->orderBy = "ORDER BY " . $tmp;
        }
        return $this;
    }

    /**
     * 设置范围
     * @param int $limit
     * @param int $offset
     * @return QueryMaker
     */
    public function setLimit($limit = 2000, $offset = 0)
    {
        if ($limit) {
            $this->limit = 'LIMIT ' . intval($offset) . ',' . intval($limit);
        } else {
            $this->limit = '';
        }
        return $this;
    }

    /**
     * 设置分组
     * @param Array /String $groupBy
     * @return QueryMaker
     */
    public function setGroupBy($groupBy)
    {
        if (empty($groupBy)) {
            return $this;
        }
        if (is_array($groupBy)) {
            $tmp = array();
            foreach ($groupBy as $v) {
                $tmp[] = $this->_prepareFieldName($v);
            }
            $this->groupBy = 'GROUP BY ' . implode(",", $tmp);
        } else {
            $this->groupBy = 'GROUP BY ' . $this->_prepareFieldName($groupBy);
        }
        return $this;
    }

    /**
     * 设置数据
     * @param Array $data
     * @return QueryMaker
     */
    public function setData(Array $data)
    {
        if ($data) {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * 获取相关SQL
     * @param boolean $raw
     * @return String $sql
     */
    public function getSQL($raw = FALSE)
    {
        $sql = $this->_getSQL();
        if($raw){
            foreach ($this->prepared as $key => $value) {
                $value = "'" . addslashes($value) . "'";
                $sql = str_replace($key, $value, $sql);
            }
        }
        return $sql;
    }

    /**
     * 返回Prepare过的数据
     * @return Array
     */
    public function getData()
    {
        $sql = $this->_getSQL();
        $data = array();
        foreach ($this->prepared as $key => $value) {
            if (strpos($sql, $key) !== false) {
                $key = ltrim($key, ':');
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * 拼SQL
     * @return string
     */
    private function _getSQL()
    {
        if($this->sql){
            return $this->sql;
        }
        switch ($this->operation) {
            case 'INSERT':
                $sql = 'INSERT INTO  ' . $this->tableName . ' SET ' . $this->_getData();
                break;
            case 'UPDATE':
                $sql = 'UPDATE ' . $this->tableName . ' SET ' . $this->_getData() . ' ' . $this->where;
                break;
            case 'REPLACE':
                $sql = 'REPLACE INTO  ' . $this->tableName . ' SET ' . $this->_getData();
                break;
            case 'DELETE':
                $sql = 'DELETE FROM ' . $this->tableName . ' ' . $this->where;
                break;
            case 'COUNT':
                $sql = 'SELECT COUNT(*) AS `count`  FROM ' . $this->tableName . ' ' . $this->where . ' ' . $this->groupBy;
                break;
            case 'SELECT':
            default:
                $sql = 'SELECT ' . $this->_getField() . ' FROM ' . $this->tableName . ' ' . $this->where . ' ' . $this->orderBy . ' ' . $this->groupBy . ' ' . $this->limit;
                break;
        }
        return $this->sql = $sql;
    }

    /**
     * 获取字段名称
     * @return String
     */
    private function _getField()
    {
        if (empty($this->field)) {
            $field = '*';
        } else {
            $tmp = array();
            foreach ($this->field as $v) {
                if ($this->customField) {
                    $tmp[] = $v;
                }
                else {
                    $tmp[] = $this->_prepareFieldName($v);
                }
            }
            $field = implode(",", $tmp);
        }
        return $field;
    }

    /**
     * 获取prepare后的数据
     * @return String
     */
    private function _getData()
    {
        $data = array();
        foreach ($this->data as $key => $value) {
            //简单字段检查
            if(($this->field AND !in_array($key, $this->field)) || is_null($value)) {
                continue;
            }
            $data[$key] = $this->_prepareFieldName($key) . ' = ' . $this->_prepareData($value);
        }
        $data = implode(",", $data);
        return $data;
    }

    /**
     * 过滤字段名，表名
     * @param String $value
     * @return string
     */
    private function _prepareFieldName($value)
    {
        $value = strtr($value, array(' ' => '', '`' => ''));
        $value = "`" . $value . "`";
        return $value;
    }

    /**
     * prepare替换
     * @param String $value
     * @return string
     */
    private function _prepareData($value)
    {
        $count = count($this->prepared);
        $key = self::PREPARE_PREFIX . $count . '_';
        $this->prepared[$key] = $value;
        return $key;
    }

}

