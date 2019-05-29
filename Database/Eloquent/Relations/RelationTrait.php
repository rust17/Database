<?php

namespace Database\Eloquent\Relations;

/**
 * 关联关系
 *
 */
trait RelationTrait
{
	protected function hasMany($relatedClass, $foreignKey, $localKey = 'id')
	{
		return new HasMany($relatedClass, $foreignKey, $localKey, $this);
	}

	protected function hasOne($relatedClass, $foreignKey, $localKey = 'id')
	{
		return new HasOne($relatedClass, $foreignKey, $localKey, $this);
	}
}