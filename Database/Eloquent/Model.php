<?php

namespace Database\Eloquent;

use Database\Builder;
use Database\Eloquent\Relations\RelationTrait;

/**
 * 模型
 *
 */
class Model
{
	use RelationTrait;

	private function build()
	{
		return new Builder($this, get_called_class());
	}

	public function __call($method, $parameters)
	{
		if (method_exists($this, $method) === false) {
			return $this->build()->$method(...$parameters);
		}
	}

	public static function __callStatic($method, $parameters)
	{
		return (new static)->$method(...$parameters);
	}

	public function __get($key)
	{
		//
	}
}