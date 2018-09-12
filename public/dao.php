<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-11
 * Time: 17:45
 */

namespace DB;

/**
 * 强制单表操作，如果需要多表查询，使用medoo原生查询
 * 单表操作自动缓存处理
 * 如果多表操作，缓存需要自己处理
 * @package DB
 */
interface IDao
{
    function findById($table, $id);

    function findByIds($table, $ids);

    function findOne($table, $where);

    function findMore($table, $where);

    function insert($table, $data);

    function save($table, $data, $where);

    function del($table, $where);

    function count($table, $where);

    function max($table, $column, $where);

    function min($table, $column, $where);

    function avg($table, $column, $where);

    function sum($table, $column, $where);

    function clearCacheByIds($table, $ids);

    function clearCache($table, $where);

    function query($query);
}

class Dao implements IDao
{
    protected $container;
    protected $db;
    protected $redis;
    protected $logger;
    protected $expire = 3600;

    public function __construct($container)
    {
        if (empty($container)) {
            throw new \Exception("container not configured");
        }
        $this->container = $container;
    }

    public function getRedis($type = '')
    {
        if (!isset($this->container['redis']) && empty($this->container['redis'])) {
            throw new \Exception("redis not configured");
        }
        $this->redis = $this->container['redis'];
        return $this->redis;
    }

    public function getDb($type = 'slave')
    {
        if (!isset($this->container[$type])) {
            throw new \Exception("database not configured");
        }
        $this->db = $this->container[$type];
        return $this->db;
    }

    public function getLogger()
    {
        if (!isset($this->container['logger'])) {
            throw new \Exception("logger not configured");
        }
        $this->logger = $this->container['logger'];
        return $this->logger;
    }

    protected function mkQuery($table, $where, $column = '*')
    {
        $map = [];
        $query = $this->getDb()->selectContext($table, $map, null, $column, $where);
        $clean_query = $this->getDb()->generate($query, $map);
        return $clean_query;
    }

    /**
     * 模糊匹配key，遍历删除
     * @param string $wildkey
     * @return boolean
     */
    public function mdel($wildkey)
    {
        $keys = $this->redis->keys($wildkey);
        if (empty($keys)) {
            return false;
        }
        foreach ($keys as $k) {
            $this->redis->del($k);
        }
        return true;
    }

    /**
     * @param $table
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    function findById($table, $id)
    {
        $redis_key = $table . ":" . "id:" . $id;
        $cache = $this->getRedis()->get($redis_key);
        if (!empty($cache)) {
            if ($cache == "[]") {//如果是空 返回空数组
                return array();
            }
            return $cache;
        }
        $item = $this->getDb()->get($table, '*', ['id' => $id]);
        if (!empty($item)) {
            $this->getRedis()->set($redis_key, json_encode($item),'EX', $this->expire);
        } else {
            $item = "";
        }
        return $item;
    }

    /**
     * @param $table
     * @param $ids
     * @return mixed
     * @throws \Exception
     */
    function findByIds($table, $ids)
    {
        $ret = [];
        foreach ($ids as $id) {
            $ret[] = $this->findById($table, $id);
        }
        return $ret;
    }

    /**
     * @param $table
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    function findOne($table, $where)
    {
        $query = $this->mkQuery($table, $where);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return json_decode($cache,true);
        }
        $row = $this->getDb()->get($table, '*', $where);
        if (!empty($row)) {
            $this->getRedis()->set($redis_query_key, json_encode($row),'EX', $this->expire);
        }
        return $row;
    }

    /**
     * @param $table
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    function findMore($table, $where)
    {
        $query = $this->mkQuery($table, $where);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return json_decode($cache,true);
        }
        $rows = $this->getDb->select($table, '*', $where);
        if (!empty($rows)) {
            $this->getRedis()->set($redis_query_key, json_encode($rows),'EX', $this->expire);
        }
        return $rows;
    }

    /**
     * @param $table
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    function insert($table, $data)
    {
        $database = $this->getDb('master');
        $database->insert($table, $data);
        $lastInsertId = $database->id();
        if (false !== $lastInsertId) {
            $redis_query_key = "query_cache:{$table}:*";
            $this->getRedis()->mdel($redis_query_key);
            $this->findById($table, $lastInsertId);
            return $lastInsertId;
        }
        return false;
    }

    /**
     * @param $table
     * @param $data
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    function save($table, $data, $where)
    {
        $rows = $this->getDb('master')->select($table, '*', $where);
        if (!empty($rows)) {
            foreach ($rows as $r) {
                $redis_key = $table . ":" . "id:" . $r['id'];
                $this->getRedis()->del($redis_key);
            }
            $redis_query_key = "query_cache:{$table}:*";
            $this->getRedis()->mdel($redis_query_key);
        }
        //成功则返回PDOStatement，否则就是false
        return $this->getDb('master')->update($table, $data, $where);
    }

    /**
     * @param $table
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    function del($table, $where)
    {
        $data = $this->getDb('master')->select($table, '*', $where);
        if (!empty($data)) {
            foreach ($data as $r) {
                $redis_key = $table . ":" . "id:" . $r['id'];
                $this->getRedis()->del($redis_key);
            }
        }
        //成功则返回PDOStatement，否则就是false
        $ret = $this->getDb('master')->delete($table, $where);
        if (false !== $ret) {
            $redis_query_key = "query_cache:{$table}:*";
            $this->getRedis()->mdel($redis_query_key);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $table
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    function count($table, $where)
    {
        $query = $this->mkQuery($table, $where);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return $cache;
        }
        $cnt = $this->getDb()->count($table, $where);
        $this->getRedis()->set($redis_query_key, $cnt, 'EX', $this->expire);
        return $cnt;
    }

    /**
     * @param $table
     * @param $ids
     * @return mixed
     * @throws \Exception
     */
    function clearCacheByIds($table, $ids)
    {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $redis_key = $table . ":" . "id:" . $id;
                $this->getRedis()->del($redis_key);
            }
        } else {
            $redis_key = $table . ":" . "id:" . $ids;
            $this->getRedis()->del($redis_key);
        }
    }

    /**
     * @param $table
     * @param $where
     * @return mixed
     * @throws \Exception
     */
    function clearCache($table, $where)
    {
        $data = $this->getDb()->select($table, '*', $where);
        if (!empty($data)) {
            $this->clearCacheByIds($table, array_column($data, 'id'));
        }
    }

    function query($query)
    {
        return $this->getDb()->query($query);
    }

    function max($table, $column, $where)
    {
        $query = $this->mkQuery($table, $where, $column);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return $cache;
        }
        $max = $this->getDb()->max($table, $column, $where);
        $this->getRedis()->set($redis_query_key, $max,'EX', $this->expire);
        return $max;
    }

    function min($table, $column, $where)
    {
        $query = $this->mkQuery($table, $where, $column);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return $cache;
        }
        $min = $this->getDb()->min($table, $column, $where);
        $this->getRedis()->set($redis_query_key, $min,'EX', $this->expire);
        return $min;
    }

    function avg($table, $column, $where)
    {
        $query = $this->mkQuery($table, $where, $column);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return $cache;
        }
        $ret = $this->getDb()->avg($table, $column, $where);
        $this->getRedis()->set($redis_query_key, $ret,'EX', $this->expire);
        return $ret;
    }

    function sum($table, $column, $where)
    {
        $query = $this->mkQuery($table, $where, $column);
        $redis_query_key = "query_cache:{$table}:" . md5(trim($query));
        $cache = $this->getRedis()->get($redis_query_key);
        if (!empty($cache)) {
            return $cache;
        }
        $ret = $this->getDb()->sum($table, $column, $where);
        $this->getRedis()->set($redis_query_key, $ret,'EX', $this->expire);
        return $ret;
    }
}