<?php

namespace Database;

use Closure;
use Database\Query\WhereTrait;
use Database\Query\AggregateTrait;
use Database\Query\Builder as Query;
use Database\Query\JoinClause;

/**
 * 构建 SQL 语句
 *
 */
class Builder
{
	use WhereTrait;
	use AggregateTrait;

	/**
	 * 模型对象
	 *
	 */
	public $model;

	/**
	 * 模型类名
	 *
	 */
	protected $modelName;

	/**
	 * 构建好的 SQL
	 *
	 */
	public $sql;

	/**
	 * 构建好的 sql insert 部分 
	 *
	 */
	public $insertSql = [];

	/**
	 * 构建好的 sql update 部分 
	 *
	 */
	public $updateSql = [];

	/**
	 * where 条件
	 *
	 */
	protected $where = [];

	/**
	 * 构建好的 where 条件
	 *
	 */
	protected $whereClause;

	/**
	 * 需要查找的列
	 *
	 */
	protected $columns = [];

	/**
	 * 连表的连接字段、连接类型
	 *
	 */
	protected $join = [];

	/**
	 * offset 与 limit
	 *
	 */
	protected $offset = [];

	/**
	 * group by
	 *
	 */
	protected $groupBy = [];

	/**
	 * order by
	 *
	 */
	protected $orderBy = [];

	public function __construct($model, $modelName)
	{
		$this->model     = $model;
		$this->modelName = $modelName;
		$this->connect   = new Connect($this);
	}

	/**
	 * 构建 where 条件
	 *
	 * @return $this
	 */
	public function where()
	{
		$receive = func_get_args();
		
		// 如果 $column[0] 是闭包，则认为需要在闭包中构造查询语句
		if ($receive[0] instanceOf Closure) {
			// 每一个闭包是一个独立条件，构造好后作为一个子条件嵌套
			$query = new Query();
			call_user_func_array($receive[0], [$query]);
			array_push($this->where, $query->where);

			return $this;
		}
		// 如果 $column[0][0] 是闭包，则认为需要在闭包中构造查询语句
		if ($receive[0][0] instanceOf Closure) {
			// 每一个闭包是一个独立条件，构造好后作为一个子条件嵌套
			$query = new Query();
			call_user_func_array($receive[0][0], [$query]);
			array_push($this->where, $query->where);

			return $this;
		}
		// $receive 是一维数组 且 $receive 有两个值
		if ($this->array_dim($receive) === 1 && count($receive) === 2) {
			return $this->addWhere($receive[0], $receive[1]);
		}
		// $receive 是一维数组 且 $receive 有三个值
		if ($this->array_dim($receive) === 1 && count($receive) === 3) {
			return $this->addWhere($receive[0], $receive[1], $receive[2]);
		}
		// $receive 是一维数组 且 $receive 有四个值
		if ($this->array_dim($receive) === 1 && count($receive) === 4) {
			return $this->addWhere($receive[0], $receive[1], $receive[2], $receive[3]);
		}
		// $receive 是二维数组，且 $receive[0] 有两个值
		if ($this->array_dim($receive) === 2 && count($receive[0]) === 2) {
			return $this->where($receive[0][0], $receive[0][1]);
		}
		// $receive 是二维数组，且 $receive[0] 有三个值
		if ($this->array_dim($receive) === 2 && count($receive[0]) === 3) {
			return $this->where($receive[0][0], $receive[0][1], $receive[0][2]);
		}
		// $receive 是三维数组，且 $receive 有一个值，则认为这是几个并列的条件
		if ($this->array_dim($receive) === 3 && count($receive) === 1) {
			$current = 0;
			foreach ($receive[0] as $item) {
				$this->where($item);
				$current++;
				if ($current == count($receive[0])) {
					// 执行到循环的最后一次需要停止
					return $this;
				}
			}
		}
		// $receive 是四维数组，则认为这是三维数组包裹多了一层
		if ($this->array_dim($receive) === 4) {
			return $this->where($receive[0][0]);
		}

		return $this;
	}

	/**
	 * 连表查询 innor join
	 *
	 * @return $this
	 */
	public function join()
	{
		$receive = func_get_args();

		// $receive 是一维数组 且 $receive 有四个值
		if ($this->array_dim($receive) === 1 && count($receive) === 4) {
			list($table, $first, $operator, $second) = $receive;
		}
		// $receive 是一维数组 且 $receive 第二个值是闭包
		if ($this->array_dim($receive) === 1 && $receive[1] instanceOf Closure) {
			$join = new JoinClause();
			$join->table = $receive[0];
			call_user_func_array($receive[1], [$join]);

			array_push($this->where, $join->where);
			array_push($this->join, $join->join);

			return $this;
		}
		// $receive 是二维数组 且 $receive[0][1] 是闭包
		if ($this->array_dim($receive) === 2 && $receive[0][1] instanceOf Closure) {
			$join = new JoinClause();
			$join->table = $receive[0][0];
			call_user_func_array($receive[0][1], [$join]);

			array_push($this->where, $join->where);
			array_push($this->join, $join->join);

			return $this;
		}
		// $receive 是二维数组 且 $receive 有一个值
		if ($this->array_dim($receive) === 2 && count($receive) === 1) {
			list($table, $first, $operator, $second) = $receive[0];
		}

		return $this->realJoin($table, $first, $operator, $second, 'innor');
	}

	/**
	 * 连表查询 left join
	 *
	 * @return $this
	 */
	public function leftjoin()
	{
		$receive = func_get_args();
		// $receive 是一维数组 且 $receive 有四个值
		if ($this->array_dim($receive) === 1 && count($receive) === 4) {
			list($table, $first, $operator, $second) = $receive;
		}
		// $receive 是二维数组 且 $receive 有一个值
		if ($this->array_dim($receive) === 2 && count($receive) === 1) {
			list($table, $first, $operator, $second) = $receive[0];
		}

		return $this->realJoin($table, $first, $operator, $second, 'left');
	}

	/**
	 * 连表查询 right join
	 *
	 * @return $this
	 */
	public function rightjoin()
	{
		$receive = func_get_args();
		// $receive 是一维数组 且 $receive 有四个值
		if ($this->array_dim($receive) === 1 && count($receive) === 4) {
			list($table, $first, $operator, $second) = $receive;
		}
		// $receive 是二维数组 且 $receive 有一个值
		if ($this->array_dim($receive) === 2 && count($receive) === 1) {
			list($table, $first, $operator, $second) = $receive[0];
		}

		return $this->realJoin($table, $first, $operator, $second, 'right');
	}

	/**
	 * 确定查询的列
	 *
	 * @return $this
	 */
	public function select()
	{		
		return $this->realSelect(func_get_args());
	}

	/**
	 * 释放资源
	 */
	protected function releaseResource()
	{
		unset($this->whereClause);
	}

	/**
	 * 根据主键查询一条记录 或 根据多个主键查询多条记录
	 *
	 * @return array
	 */
	public function find()
	{
		$receive = func_get_args();

		// $receive 是一维数组 且 $receive 有一个值
		if ($this->array_dim($receive) === 1 && count($receive) === 1) {
			$result = $this->whereKey($receive[0])->decideWhichSelect()->connect->get();
			$this->releaseResource();
			return $result;
		}
		// $receive 是二维数组 且 $receive[0] 有一个值
		if ($this->array_dim($receive) === 2 && count($receive[0]) === 1) {
			return $this->find($receive[0][0]);
		}

		// $receive 是三维数组 且 $receive[0][0] 有多个值
		if ($this->array_dim($receive) === 3 && count($receive[0][0]) > 1) {
			return $this->find($receive[0][0]);
		}

		return $this->findMany($receive[0]);		
	}

	/**
	 * 根据多个主键查询多条记录
	 *
	 * @param array $ids
	 * @return array
	 */
	protected function findMany($ids)
	{
		if (empty($ids)) {
			return $this->model;
		}

		$result = $this->whereKey($ids)->decideWhichSelect()->connect->get();
		$this->releaseResource();
		return $result;
	}

	/**
	 * 获取结果集中的第一条记录
	 * 
	 * @return array
	 */
	public function first()
	{
		return $this->offset(0)->limit(1)->get();
	}

	/**
	 * 获取记录集
	 *
	 * @return array
	 */
	public function get()
	{
		$result = $this->decideWhichSelect()->connect->get();
		$this->releaseResource();
		return $result;
	}

	/**
	 * 获取结果集条数
	 *
	 * @return int
	 */
	public function count()
	{
		$result = $this->decideWhichSelect()->connect->count();
		$this->releaseResource();
		return $result;
	}

	/**
	 * 是否存在
	 *
	 * @return boolean 
	 */
	public function exists()
	{
		$result = $this->decideWhichSelect()->connect->count();
		$this->releaseResource();
		return ($result === 0) ? false : true;
	}

	/**
	 * 插入记录
	 *
	 * @param array $arr
	 * @return int
	 */
	public function insert($arr)
	{
		return $this->prepareFieldsAndValues($arr)->prepareInsertSql()->connect->insert();
	}

	/**
	 * 更新记录
	 *
	 * @param array $arr
	 * @return boolean
	 */
	public function update($arr)
	{
		return $this->formatUpdatedParameters($arr)->prepareUpdateSql()->connect->update();
	}

	/**
	 * 自增
	 *
	 * @param string $column
	 * @param int $value 1
	 * @return boolean
	 */
	public function increment($column, $value = 1)
	{
		return $this->update([$column => $column . '+' .$value]);
	}

	/**
	 * 自减
	 *
	 * @param string $column
	 * @param int $value 1
	 * @return boolean
	 */
	public function decrement($column, $value = 1)
	{
		return $this->update([$column => $column . '-' .$value]);
	}

	/**
	 * 物理删除
	 *
	 * @return boolean
	 */
	public function delete()
	{
		return $this->prepareDeleteSql()->connect->delete();
	}

	/**
	 * 软删除
	 *
	 * @param string $column 'is_deleted'
	 * @return boolean
	 */
	public function softDeletes($column = 'is_deleted', $value = 1)
	{
		return $this->update([$column => $value]);
	}

	/**
	 * 决定使用哪个 select 方法
	 *
	 */
	protected function decideWhichSelect()
	{
		if ($this->join) {
			return $this->prepareJointSelectSql();
		}

		return $this->prepareSelectSql();
	}

	/**
	 * 合成连表查询的 select sql 语句
	 *
	 * @return $this
	 */
	protected function prepareJointSelectSql()
	{
		$columns = implode(',', $this->columns);
		$tables  = [];
		$prefix  = "{$this->model->table} ";
		foreach ($this->join as $index => $join) {
			$i     = $index + 2;
			$table = " {$join['type']} join {$join['table']} on {$join['first']} {$join['operator']} {$join['second']} ";
			array_push($tables, $table);
		}
		$tables            = $prefix . implode('', $tables);

		$this->whereClause = $this->combineWhere()->orderBySet()->offsetSet()->groupBySet()->escapeFirstJoiner($this->whereClause);
		$this->sql         = sprintf("select %s from %s where %s", $columns, $tables, $this->whereClause);

		return $this;
	}

	/**
	 * 合成 select sql 语句
	 *
	 * @return $this
	 */
	protected function prepareSelectSql()
	{
		$this->whereClause = $this->combineWhere()->orderBySet()->offsetSet()->groupBySet()->escapeFirstJoiner($this->whereClause);
		$this->sql = sprintf("select * from {$this->model->table} where %s", $this->whereClause);

		return $this;
	}

	/**
	 * 合成 insert sql 语句
	 *
	 * @return $this
	 */
	protected function prepareInsertSql()
	{
		$this->sql = sprintf("insert into {$this->model->table} %s values %s", $this->insertSql['fields'], $this->insertSql['values']);

		return $this;
	}

	/**
	 * 合成 update sql 语句
	 *
	 * @return $this
	 */
	protected function prepareUpdateSql()
	{
		// 准备 where 条件
		$this->whereClause = $this->combineWhere()->escapeFirstJoiner($this->whereClause);
		$this->sql = sprintf("update {$this->model->table} set %s where %s", $this->updateSql, $this->whereClause);

		return $this;
	}

	/**
	 * 合成 delete sql 语句
	 *
	 * @return $this
	 */
	protected function prepareDeleteSql()
	{
		// 准备 where 条件
		$this->whereClause = $this->combineWhere()->escapeFirstJoiner($this->whereClause);
		$this->sql = sprintf("delete from {$this->model->table} where %s", $this->whereClause);

		return $this;
	}

	/**
	 * 格式化 update 的字段和值
	 *
	 * @param array $parameters
	 * @return array
	 */
	protected function formatUpdatedParameters($parameters)
	{
		foreach ($parameters as $fieldKey => $fieldValue) {
			// 如果字段的形式是 字段+/-数字，则认为不用加 ''
			if (preg_match('/[a-zA-Z]+(\+|\-)\d/m', $fieldValue)) {
				array_push($this->updateSql, "`$fieldKey`=$fieldValue");
			} else {
				array_push($this->updateSql, "`$fieldKey`='$fieldValue'");
			}
		}
		$this->updateSql = implode(',', $this->updateSql);

		return $this;
	}

	/**
	 * 将 model 传过来的参数数组分解
	 *
	 * @param array $parameters
	 * @return array
	 */
	protected function prepareFieldsAndValues($parameters)
	{
		// 如果 $parameters 是一个一维数组
		if ($this->array_dim($parameters) === 1) {
			$this->insertSql['fields'] = "(" . $this->array_wrap_with_separator(array_keys($parameters)) . ")";
			$this->insertSql['values'] = "(" . $this->array_wrap_with_quote(array_values($parameters)) . ")";
		}
		// 如果 $parameters 是一个二维数组 且 $parameters 有一个值
		if ($this->array_dim($parameters) === 2 && count($parameters) === 1) {
			return $this->prepareFieldsAndValues($parameters[0]);
		}
		// 如果 $parameters 是一个二维数组 且 $parameters 有多个值
		if ($this->array_dim($parameters) === 2 && count($parameters) > 1) {
			$this->insertSql['fields'] = "(" . $this->array_wrap_with_separator(array_keys($parameters[0])) . ")";
			$valueArr = [];
			foreach ($parameters as $item) {
				array_push($valueArr, "(" . $this->array_wrap_with_quote(array_values($item)) . ")");
			}
			$this->insertSql['values'] = implode(',', $valueArr);
		}
		// 如果 $parameters 是一个三维数组
		if ($this->array_dim($parameters) === 3) {
			return $this->prepareFieldsAndValues($parameters[0]);
		}

		return $this;	
	}

	public function __call($method, $parameters)
	{
		if (method_exists($this, $method) === false) {
			return $this->connect->$method(...$parameters);
		}
	}
}