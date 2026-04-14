<?php

namespace Rizkussef\LaravelCrudApi\Traits;

trait FilterQueryTrait
{
    /**
     * Apply filters to query based on filter array
     * 
     * Filter format examples:
     * - Simple equality: ['status' => 'active']
     * - Operators: ['age' => ['operator' => '>', 'value' => 18]]
     * - Between: ['price' => ['operator' => 'between', 'value' => [100, 500]]]
     * - In: ['status' => ['operator' => 'in', 'value' => ['active', 'pending']]]
     * - Like: ['name' => ['operator' => 'like', 'value' => 'John']]
     * - Is Null: ['deleted_at' => ['operator' => 'null']]
     * - Is Not Null: ['verified_at' => ['operator' => '!null']]
     */
    protected function applyFilters($query, array $filters = [])
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // If value is array with operator, use advanced filtering
            if (is_array($value) && isset($value['operator'])) {
                $operator = $value['operator'];
                $val = $value['value'] ?? null;

                switch ($operator) {
                    case '=':
                    case 'eq':
                        $query->where($key, '=', $val);
                        break;

                    case '!=':
                    case '<>':
                    case 'neq':
                        $query->where($key, '!=', $val);
                        break;

                    case '>':
                    case 'gt':
                        $query->where($key, '>', $val);
                        break;

                    case '>=':
                    case 'gte':
                        $query->where($key, '>=', $val);
                        break;

                    case '<':
                    case 'lt':
                        $query->where($key, '<', $val);
                        break;

                    case '<=':
                    case 'lte':
                        $query->where($key, '<=', $val);
                        break;

                    case 'like':
                    case 'contains':
                        $query->where($key, 'LIKE', "%{$val}%");
                        break;

                    case 'starts_with':
                        $query->where($key, 'LIKE', "{$val}%");
                        break;

                    case 'ends_with':
                        $query->where($key, 'LIKE', "%{$val}");
                        break;

                    case 'in':
                        $query->whereIn($key, (array)$val);
                        break;

                    case 'not_in':
                        $query->whereNotIn($key, (array)$val);
                        break;

                    case 'between':
                        if (is_array($val) && count($val) === 2) {
                            $query->whereBetween($key, $val);
                        }
                        break;

                    case 'not_between':
                        if (is_array($val) && count($val) === 2) {
                            $query->whereNotBetween($key, $val);
                        }
                        break;

                    case 'null':
                        $query->whereNull($key);
                        break;

                    case '!null':
                    case 'not_null':
                        $query->whereNotNull($key);
                        break;

                    case 'exists':
                        if ($val === true) {
                            $query->whereNotNull($key);
                        } elseif ($val === false) {
                            $query->whereNull($key);
                        }
                        break;

                    case 'date':
                        $query->whereDate($key, '=', $val);
                        break;

                    case 'year':
                        $query->whereYear($key, '=', $val);
                        break;

                    case 'month':
                        $query->whereMonth($key, '=', $val);
                        break;

                    case 'day':
                        $query->whereDay($key, '=', $val);
                        break;
                }
            } else {
                // Default behavior - treat as LIKE search for strings, exact match otherwise
                if (is_string($value) && strlen($value) > 0) {
                    $query->where($key, 'LIKE', "%{$value}%");
                } else {
                    $query->where($key, $value);
                }
            }
        }
        return $query;
    }
}
