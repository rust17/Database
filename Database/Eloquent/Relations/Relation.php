<?php

namespace Database\Eloquent\Relations;

/**
 * 关联模型类
 *
 */
class Relation
{
	/**
	 * 关联模型
	 *
	 */
	protected $relatedModel;

	/**
	 * 关联模型外键
	 *
	 */
	protected $foreignKey;

	/**
	 * 父模型
	 *
	 */
	protected $parent;

	/**
	 * 父模型主键
	 *
	 */
	protected $localKey;

	public function __construct($relatedClass, $foreignKey, $localKey, $parent)
	{
		$this->relatedModel = new $relatedClass($this);
		$this->foreignKey   = $foreignKey;
		$this->parent       = $parent;
		$this->localKey     = $localKey;
	}

	public function __call($method, $parameters)
	{
		return $this->relatedModel->$method($parameters);
	}
}