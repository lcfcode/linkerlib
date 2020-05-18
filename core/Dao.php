<?php
/**
 * @link https://gitee.com/lcfcode/linker
 * @link https://github.com/lcfcode/linker
 */

namespace swap\core;

abstract class Dao
{
    private $tabName;
    private $tabId;
    private $tabField;

    private $writeClient;
    private $readClient;

    /**
     * @return array
     * @author LCF
     * @date
     * 抽象方法，用于返回表名，默认主键名和字段；表名必须返回，其他选填
     */
    abstract public function connectInfo(): array;

    /**
     * @param bool $flag
     * @return DbInterface
     * @author LCF
     * @date 2020/1/18 21:07
     */
    private function connect($flag = true)
    {
        $result = $this->connectInfo();
        if (!isset($result['table'])) {
            trigger_error('connectInfo function return data err!', E_USER_ERROR);
        }
        $this->tabName = $result['table'];
        $this->tabId = isset($result['default_id']) ? $result['default_id'] : 'id';
        $this->tabField = isset($result['field']) ? $result['field'] : ['*'];
        $dbKey = isset($result['db']) ? $result['db'] : 'db';
        $application = \RegTree::get('app.application');
        $config = $application->config()['global.config'][$dbKey];
        $separate = isset($config['separate']) ? $config['separate'] : false;
        if (true === $separate) {
            $this->readClient = $application->dbInstance($config['read_db'], 'linker_read');
            $this->writeClient = $application->dbInstance($config);
        } else {
            $this->readClient = $this->writeClient = $application->dbInstance($config);
        }
        return $flag ? $this->readClient : $this->writeClient;
    }

    /**
     * @return DbInterface
     * @author LCF
     * @date 2020/1/18 21:06
     */
    private function readClient()
    {
        if ($this->readClient) {
            return $this->readClient;
        }
        return $this->connect();
    }

    /**
     * @return DbInterface
     * @author LCF
     * @date 2020/1/18 21:06
     */
    private function writeClient()
    {
        if ($this->writeClient) {
            return $this->writeClient;
        }
        return $this->connect(false);
    }

    /**
     * @return DbInterface
     * @author LCF
     * @date 2019/10/26 16:49
     * 读写分离尽量别用该方法
     */
    public function db()
    {
        return $this->writeClient()->db();
    }

    public function clientInfo()
    {
        return $this->writeClient()->clientInfo();
    }

    public function serverInfo()
    {
        return $this->writeClient()->serverInfo();
    }

    public function getLastSql($flag = true)
    {
        $sql = $this->writeClient()->getLastSql($flag);
        if (empty($sql)) {
            $sql = $this->readClient()->getLastSql($flag);
        }
        return $sql;
    }

    public function insert($data)
    {
        return $this->writeClient()->insert($this->tabName, $data);
    }

    public function updateId($id, $data)
    {
        return $this->writeClient()->updateId($this->tabName, $this->tabId, $id, $data);
    }

    public function deleteId($id)
    {
        return $this->writeClient()->deleteId($this->tabName, $this->tabId, $id);
    }

    public function update($data, $where)
    {
        return $this->writeClient()->update($this->tabName, $data, $where);
    }

    public function delete($where)
    {
        return $this->writeClient()->delete($this->tabName, $where);
    }

    public function selectId($id)
    {
        return $this->readClient()->selectId($this->tabName, $this->tabId, $id, $this->tabField);
    }

    public function selectOne($where, $order = [])
    {
        return $this->readClient()->selectOne($this->tabName, $where, $order, $this->tabField);
    }

    public function selectAll($order = [], $offset = 0, $fetchNum = 0)
    {
        return $this->readClient()->selectAll($this->tabName, $order, $offset, $fetchNum, $this->tabField);
    }

    public function selects($where = [], $order = [], $offset = 0, $fetchNum = 0)
    {
        return $this->readClient()->selects($this->tabName, $where, $order, $offset, $fetchNum, $this->tabField);
    }

    public function selectIn($field, $inWhere, $where = [], $order = [], $offset = 0, $fetchNum = 0)
    {
        return $this->readClient()->selectIn($this->tabName, $field, $inWhere, $where, $order, $offset, $fetchNum, $this->tabField);
    }

    public function query($sql, $param = [])
    {
        if (stripos(trim($sql), 'select') === 0) {
            return $this->readClient()->query($sql, $param);
        }
        return $this->writeClient()->query($sql, $param);
    }

    public function like($stringName, $content, $where = [], $isCount = false, $order = [], $offset = 0, $fetchNum = 0, $direction = 'in')
    {
        return $this->readClient()->like($this->tabName, $stringName, $content, $where, $isCount, $order, $offset, $fetchNum, $direction, $this->tabField);
    }

    public function count($where = [], $columnName = '*', $distinct = false)
    {
        return $this->readClient()->count($this->tabName, $where, $columnName, $distinct);
    }

    public function min($columnName, $where = [])
    {
        return $this->readClient()->min($this->tabName, $columnName, $where);
    }

    public function max($columnName, $where = [])
    {
        return $this->readClient()->max($this->tabName, $columnName, $where);
    }

    public function avg($columnName, $where = [])
    {
        return $this->readClient()->avg($this->tabName, $columnName, $where);
    }

    public function sum($columnName, $where = [])
    {
        return $this->readClient()->sum($this->tabName, $columnName, $where);
    }

    public function insertMultiple($multipleInsertData, $keys = [])
    {
        return $this->writeClient()->insertMultiple($this->tabName, $multipleInsertData, $keys);
    }

    public function beginTransaction()
    {
        return $this->writeClient()->beginTransaction();
    }

    public function commitTransaction()
    {
        return $this->writeClient()->commitTransaction();
    }

    public function rollbackTransaction()
    {
        return $this->writeClient()->rollbackTransaction();
    }

    public function close()
    {
        $this->writeClient()->close();
        $this->readClient()->close();
    }
}