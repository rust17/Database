<?php

namespace Database\Query;

/**
 * 与聚合、排序有关的操作
 *
 */
trait AggregateTrait
{
	/**
	 * 构建 order by 语句，默认升序
	 *
	 * @param string $column
	 * @param string $sort
	 * @return $this
	 */
	public function orderBy($column, $sort = 'asc')
	{
		if (! in_array($sort, ['asc', 'desc'])) {
			$sort = 'asc';
		}

		$this->orderBy['column'] = $column;
		$this->orderBy['sort']   = $sort;
		
		return $this;
	}

	/**
	 * 默认使用 created_at 倒序排列
	 *
	 * @param string $column
	 * @return $this
	 */
	public function latest($column = 'created_at')
	{
		$this->orderBy['column'] = $column;
		$this->orderBy['sort']   = 'desc';
		
		return $this;
	}

	/**
	 * 默认使用 created_at 升序排列
	 *
	 * @param string $column
	 * @return $this
	 */
	public function oldest($column = 'created_at')
	{
		$this->orderBy['column'] = $column;
		$this->orderBy['sort']   = 'asc';
		
		return $this;
	}

	/**
	 * 构建聚合语句
	 *
	 * @param string $column
	 * @return $this
	 */
	public function groupBy(...$column)
	{
		$this->groupBy = $column;
		
		return $this;
	}

	/**
	 * 添加偏移
	 *
	 * @param int $value
	 * @return $this
	 */
	public function offset($value)
	{
		$this->offset['offset'] = $value;

		return $this;
	}

	/**
	 * 添加限制
	 *
	 * @param int $value
	 * @return $this
	 */
	public function limit($value)
	{
		$this->offset['limit'] = $value;
		
		return $this;
	}

	/**
	 * 构建 limit,offset 部分
	 *
	 * @return $this
	 */
	protected function offsetSet()
	{
		if (isset($this->offset['offset']) 
			&& $this->offset['limit']
		) {
			$this->whereClause .= " limit " . $this->offset['offset'] . "," . $this->offset['limit'] . " ";
		}

		return $this;
	}

	/**
	 * 构建 group by 部分
	 *
	 * @return $this
	 */
	protected function groupBySet()
	{
		if ($this->groupBy) {
			$this->whereClause .= " group by " . implode(',', $this->groupBy);
		}

		return $this;
	}

	/**
	 * 构建 order by 部分
	 *
	 * @return $this
	 */
	protected function orderBySet()
	{
		if ($this->orderBy) {
			$this->whereClause .= " order by " . $this->orderBy['column'] ." ". $this->orderBy['sort'];
		}

		return $this;
	}
}