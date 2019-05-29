<?php

namespace Database\Query;

use Database\Query\WhereTrait;

/**
 * 构建连表闭包语句
 */
class JoinClause
{
	use WhereTrait;

	/**
	 * 连接的表
	 */
	public $table;

	/**
	 * where 条件
	 */
	public $where = [];

	/**
	 * 连表字段、连接类型
	 */
	public $join = [];

	/**
	 * 连表
	 *
	 * @param string $first
	 * @param string $operator
	 * @param string $second
	 * @param string $type
	 */
	public function on($first, $operator, $second, $type = 'inner')
	{
		$this->join = [
			'table'    => $this->table,
			'first'    => $first,
			'operator' => $this->validateOperator($operator),
			'second'   => $second,
			'type'     => $type,
		];

		return $this;
	}

	/**
	 * 添加连表内的闭包 where 条件
	 *
	 * @param string|array $column
	 * @param string $operator
	 * @param mixed $value
	 * @param string $boolean
	 * @return $this
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		// 如果只有两个参数，则认为运算符是 =
		if (func_num_args() == 2) {
			$value    = $operator;
			$operator = '=';
		}

		// 校验运算符
		$operator = $this->validateOperator($operator);

		// 如果运算符包括 in，需要加上 ()
		if (strpos($operator, 'in') !== false) { $value = "(". $value .")"; }

		array_push($this->where, [
			'table'    => $this->table,
			'boolean'  => $boolean,
			'column'   => $column,
			'operator' => $operator,
			'value'    => $value,
		]);

		return $this;
	}
}