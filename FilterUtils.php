<?php

namespace App\Utils;

class FilterUtils
{
    public static function buildConditions($cols, $filters, &$bindings)
    {
        $conditions = [];

        foreach ($filters as $field => $operators) {
            foreach ($operators as $operator => $value) {
                $col = $cols[$field];
                $condition = self::buildCondition($col, $field, $operator, $value, $bindings);
                if ($condition) {
                    $conditions[] = $condition;
                }
            }
        }

        return $conditions;
    }

    private static function buildCondition($col, $field, $operator, $value, &$bindings)
    {
        $placeholder = $field . '_' . ltrim($operator, '$');
        switch ($operator) {
            case '$eq':
                $bindings[$placeholder] = $value;
                return "$col = :$placeholder";
            case '$eqi':
                $bindings[$placeholder] = strtolower($value);
                return "LOWER($col) = :$placeholder";
            case '$ne':
                $bindings[$placeholder] = $value;
                return "$col <> :$placeholder";
            case '$nei':
                $bindings[$placeholder] = strtolower($value);
                return "LOWER($col) <> :$placeholder";
            case '$lt':
                $bindings[$placeholder] = $value;
                return "$col < :$placeholder";
            case '$lte':
                $bindings[$placeholder] = $value;
                return "$col <= :$placeholder";
            case '$gt':
                $bindings[$placeholder] = $value;
                return "$col > :$placeholder";
            case '$gte':
                $bindings[$placeholder] = $value;
                return "$col >= :$placeholder";
            case '$in':
                $inPlaceholders = self::createArrayBindings($field, ltrim($operator, '$'), $value, $bindings);
                return "$col IN ($inPlaceholders)";
            case '$notIn':
                $notInPlaceholders = self::createArrayBindings($field, ltrim($operator, '$'), $value, $bindings);
                return "$col NOT IN ($notInPlaceholders)";
            case '$contains':
                $bindings[$placeholder] = "%$value%";
                return "$col LIKE :$placeholder";
            case '$notContains':
                $bindings[$placeholder] = "%$value%";
                return "$col NOT LIKE :$placeholder";
            case '$containsi':
                $bindings[$placeholder] = "%".strtolower($value)."%";
                return "LOWER($col) LIKE :$placeholder";
            case '$notContainsi':
                $bindings[$placeholder] = "%".strtolower($value)."%";
                return "LOWER($col) NOT LIKE :$placeholder";
            case '$null':
                return "$col IS NULL";
            case '$notNull':
                return "$col IS NOT NULL";
            case '$between':
                $bindings[$placeholder . '_start'] = $value[0];
                $bindings[$placeholder . '_end'] = $value[1];
                return "$col BETWEEN :{$placeholder}_start AND :{$placeholder}_end";
            case '$startsWith':
                $bindings[$placeholder] = "$value%";
                return "$col LIKE :$placeholder";
            case '$startsWithi':
                $bindings[$placeholder] = strtolower($value) . "%";
                return "LOWER($col) LIKE :$placeholder";
            case '$endsWith':
                $bindings[$placeholder] = "%$value";
                return "$col LIKE :$placeholder";
            case '$endsWithi':
                $bindings[$placeholder] = "%" . strtolower($value);
                return "LOWER($col) LIKE :$placeholder";
            default:
                throw new \Exception('Unsupported filter operator: ' . $operator);
        }
    }

    private static function createArrayBindings($field, $operator, $values, &$bindings)
    {
        $placeholders = [];
        foreach ($values as $index => $value) {
            $placeholder = "{$field}_{$operator}_{$index}";
            $bindings[$placeholder] = $value;
            $placeholders[] = ":$placeholder";
        }
        return implode(', ', $placeholders);
    }
}
