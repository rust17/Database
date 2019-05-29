<?php

namespace Database\Query;

use Database\Query\WhereTrait;

/**
 * 构建 sql 闭包
 *
 */
class Builder
{
	use WhereTrait;

	/**
	 * where 条件
	 *
	 */
	public $where = [];

	/**
	 * 添加闭包内的 where 条件
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
			'boolean'  => $boolean,
			'column'   => $column,
			'operator' => $operator,
			'value'    => $value,
		]);

		return $this;
	}
}