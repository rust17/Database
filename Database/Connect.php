<?php

namespace Database;

/**
 * 执行 SQL 语句
 *
 */
class Connect
{
	/**
	 * 构造实例
	 *
	 */
	protected $builder;

	/**
	 * 结果集
	 *
	 */
	protected $res = [];

	/**
	 * 数据库实例
	 */
	protected $db;

	/**
	 * 初始化，获取数据库
	 */
	public function __construct($builder)
	{
		global $empire;

		$this->db  	   = $empire;
		
		$this->builder = $builder;
	}

	/**
	 * 获取结果集
	 * 
	 * @return mixed object/array
	 */
	public function get()
	{
		$query = $this->db->query($this->builder->sql);
		while ($one = $this->db->fetch($query)) {
			array_push($this->res, $one);
		}
		// 返回数组
		return $this->res;
	}

	/**
	 * 获取结果集条数
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->db->num($this->builder->sql);
	}

	/**
	 * 插入记录
	 *
	 * @return int
	 */
	public function insert()
	{
		$this->db->query($this->builder->sql);
		return $this->db->lastid();
	}

	/**
	 * 更新记录
	 *
	 * @return boolean
	 */
	public function update()
	{
		return $this->db->query($this->builder->sql);
	}

	/**
	 * 删除记录
	 *
	 * @return boolean
	 */
	public function delete()
	{
		return $this->db->query($this->builder->sql);
	}
}