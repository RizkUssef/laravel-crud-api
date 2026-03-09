<?php

namespace Rizkussef\LaravelCrudApi\Traits;

trait RelationshipsQueryTrait
{
    /**
     * Apply relationships to query based on relationships array
     * 
     * Relationships format examples:
     * - Simple relationship: ['author']
     * - Nested relationship: ['author.posts']
     */
    protected function applyRelationships($query, array $relationships = [])
    {
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        return $query;
    }
}
