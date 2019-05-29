<?php

namespace Database\Query;

use Closure;

/**
 * 与 where 有关的操作
 *
 */
trait WhereTrait
{
	/**
	 * 构建根据主键查询的 SQL
	 *
	 * @param mixed $value
	 * @return $this
	 */
	protected function whereKey($value)
	{
		if (is_array($value)) {
			$this->whereIn('id', $value);

			return $this;
		}

		$this->where('id', $value);

		return $this;
	}

	/**
	 * 构建 where is null 条件
	 *
	 * @param string $column
	 * @return $this
	 */
	public function whereNull($column)
	{
		// 如果第一个参数是一个数组
		if (is_array($column)) {
			// $column 是一维数组 且 $column 有两个值
			if ($this->array_dim($column) === 1 && count($column) === 1) {
				return $this->whereNull($column[0]);
			}
		}

		return $this->where($column, 'is', 'null');

		return $this;
	}

	/**
	 * 构建 where is not null 条件
	 *
	 * @param string $column
	 * @return $this
	 */
	public function whereNotNull($column)
	{
		// 如果第一个参数是一个数组
		if (is_array($column)) {
			// $column 是一维数组 且 $column 有两个值
			if ($this->array_dim($column) === 1 && count($column) === 1) {
				return $this->whereNotNull($column[0]);
			}
		}

		return $this->where($column, 'not', 'null');
	}

	/**
	 * 构建 where In 条件
	 *
	 * @param string $column
	 * @param array $values
	 * @param string $boolean
	 * @return $this
	 */
	public function whereIn($column, $values = null, $boolean = 'and')
	{
		// 如果第一个参数是一个数组
		if (is_array($column)) {
			// $column 是一维数组 且 $column 有两个值
			if ($this->array_dim($column) === 2 && count($column) === 2) {
				return $this->whereIn($column[0], $column[1]);
			}
		}

		$values = $this->array_wrap_with_quote($values);
		return $this->where($column, 'in', $values, $boolean);
	}

	/**
	 * 构建 where not in 条件
	 *	 
	 * @param string $column
	 * @param array $values
	 * @param string $boolean
	 * @return $this
	 */
	public function whereNotIn($column, $values = null, $boolean = 'and')
	{
		// 如果第一个参数是一个数组
		if (is_array($column)) {
			// $column 是一维数组 且 $column 有两个值
			if ($this->array_dim($column) === 2 && count($column) === 2) {
				return $this->whereNotIn($column[0], $column[1]);
			}
		}

		$values = $this->array_wrap_with_quote($values);
		return $this->where($column, 'not in', $values, $boolean);
	}

	/**
	 * 添加数组到 where 条件
	 *
	 * @param array $column
	 * @param string $boolean
	 * @return $this
	 */
	protected function addArraysOfWhere($column, $boolean)
	{
		foreach ($column as $key => $value) {
			$this->where($value[0], $value[1], $value[2], $boolean);
		}

		return $this;
	}

	/**
	 * 将数组每个值用 '' 包裹并用 , 连接
	 *
	 * @param array
	 * @return string
	 */
	protected function array_wrap_with_quote($arr)
	{
		array_walk($arr, function (&$value) {
			$value = "'" . $value . "'";
		});

		return implode(',', $arr);
	}

	/**
	 * 将数组每个值用 `` 包裹并用 , 连接
	 *
	 * @param array
	 * @return string
	 */
	protected function array_wrap_with_separator($arr)
	{
		array_walk($arr, function (&$value) {
			$value = "`" . $value . "`";
		});

		return implode(',', $arr);
	}

	/**
	 * 计算数组维数
	 *
	 * @param array $arr
	 * @return int $count
	 */
	protected function array_dim($arr)
	{
	  	if(!is_array($arr)) {
	        return 0;
	    } else {
	        $max1 = 0;
	        foreach($arr as $item1){
	            $t1 = $this->array_dim($item1);
	            if( $t1 > $max1) $max1 = $t1;
	        }
	        return $max1 + 1;
	    }
	}

	/**
	 * 校验运算符
	 *
	 * @param string $operator
	 * @return string $operator
	 */
	protected function validateOperator($operator)
	{
		if (! in_array($operator, [
				'<', '=', '>', '<>', '!=', '>=', '<=', 'not', 'is', 'in', 'not in'
			])) {
			$operator = '=';
		}

		return $operator;
	}

	/**
	 * 添加 or where 条件
	 *
	 * @param string|array $column
	 * @param string $operator
	 * @param mixed $value
	 * @param string $boolean
	 * @return $this
	 */
	public function orWhere($column, $operator = null, $value = null, $boolean = 'or')
	{
		// 如果第一个参数是一个数组
		if (is_array($column)) {
			// $column 是一维数组 且 $column 有两个值
			if ($this->array_dim($column) === 1 && count($column) === 2) {
				return $this->orWhere($column[0], $column[1]);
			}
			// $column 是一维数组 且 $column 有三个值
			if ($this->array_dim($column) === 1 && count($column) === 3) {
				return $this->orWhere($column[0], $column[1], $column[2]);
			}			
		}

		return $this->where($column, $operator, $value, $boolean);
	}

	/**
	 * 添加 where 条件
	 *
	 * @param string|array $column
	 * @param string $operator
	 * @param mixed $value
	 * @param string $boolean
	 * @return $this
	 */
	protected function addWhere($column, $operator = null, $value = null, $boolean = 'and')
	{
		// 如果只有两个参数，则认为运算符是 =
		if (func_num_args() == 2) {
			$value    = $operator;
			$operator = '=';
		}

		// 校验运算符
		$operator = $this->validateOperator($operator);

		array_push($this->where, [
			'boolean'  => $boolean,
			'column'   => $column,
			'operator' => $operator,
			'value'    => $value,
		]);

		return $this;
	}

	/**
	 * 合并 where 条件
	 *
	 * @return $this
	 */
	protected function combineWhere()
	{
		if (! $this->where) {
			$this->whereClause = 'id > 0';
		}

		foreach ($this->where as $item) {
			$length = count($item);
			// 如果子项不是一维数组，则认为这是几个并列的条件
			// 构造时需要用括号包裹起来
			if ($this->array_dim($item) > 1) {
				// 定义一个值，保存循环次数
				$i = 0;
				// 定义一个值，保存该次循环的条件
				$whereClause = '';
				foreach ($item as $child) {
					$i ++;
					$table = $this->dbtbpre . 
							(
								$child['table'] 
								? $child['table'] . '.'
								: $this->model->table . '.'
							);

					// 如果该项值包含 ()，则不需要加引号包裹
					$child['value'] = (strpos($child['value'], '(') === false) ? '\'' . $child['value'] . '\'' : $child['value'];

					$whereClause .= " ". $child['boolean'] ." $table `". $child['column'] ."` ". $child['operator'] ." ". $child['value'] ." ";
					// 循环执行到最后一项时，加上 ()
					if ($i === $length) {
						$whereClause = $this->escapeFirstJoiner($whereClause);
						$this->whereClause .= " and (". $whereClause .")";
					}
				}
			}

			// 如果子项是一维数组，则认为这是一个独立的条件
			if ($this->array_dim($item) === 1) {
				$table = $this->dbtbpre .
						(
							$item['table'] 
							? $item['table'] . '.' 
							: $this->model->table . '.'
						);

				// 如果该项值包含 ()，则不需要加引号包裹
				$item['value'] = (strpos($item['value'], '(') === false) ? '\'' . $item['value'] . '\'' : $item['value'];

				$this->whereClause .= " ". $item['boolean'] ." ". $table ." `". $item['column'] ."` ". $item['operator'] ." ". $item['value'] ." ";
			}
		}

		return $this;
	}

	/**
	 * 去除开头第一个 and、or
	 *
	 * @param string
	 * @return string
	 */
	protected function escapeFirstJoiner($whereClause)
	{
		$whereClauseArr = explode(' ', $whereClause);
		if (in_array($whereClauseArr[1], ['and', 'or'])) {
			unset($whereClauseArr[1]);
			$whereClause = implode(' ', $whereClauseArr);
		}

		return $whereClause;
	}

	/**
	 * 将连表查询的列按照 表别名.列名 的格式返回
	 *
	 * @param array $array
	 * @return array
	 */
	protected function formatJointColumns($array)
	{
		$newColumns = [];
		foreach ($array as $index => $columns) {
			array_walk($columns, function (&$column) use ($index) {
				$table  = $this->dbtbpre . 
						(
						  	$index == 0 
						  	? $this->model->table 
						  	: $this->join[$index-1]['table']
						);

				$column = $table . '.' . $column;
			});
			array_push($newColumns, implode(',', $columns));
		}

		return $newColumns;
	}

	/**
	 * 选择需要的查询的列
	 *
	 * @param array $receive
	 * @return $this
	 */
	protected function realSelect($receive)
	{
		$this->columns = $this->formatJointColumns($receive);

		return $this;
	}

	/**
	 * 构建连表查询 sql
	 *
	 * @param string $table
	 * @param string $first
	 * @param string $operator
	 * @param string $second
	 * @param string $type
	 *
	 * @return $this
	 */
	protected function realJoin($table, $first, $operator, $second, $type = 'inner')
	{
		array_push($this->join, [
			'table'    => $table,
			'first'    => $first,
			'operator' => $this->validateOperator($operator),
			'second'   => $second,
			'type'     => $type,
		]);

		return $this;
	}
}