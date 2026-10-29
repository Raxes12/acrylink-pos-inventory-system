<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final class CaseInsensitiveSearch
{
    public static function where(Builder $query, string $column, string $value, string $boolean = 'and'): Builder
    {
        if (! preg_match('/^[A-Za-z0-9_.]+$/', $column)) {
            throw new InvalidArgumentException('Search columns must be trusted identifiers.');
        }

        $wrapped = $query->getQuery()->getGrammar()->wrap($column);

        return $query->whereRaw(
            "LOWER({$wrapped}) LIKE ?",
            ['%'.mb_strtolower($value, 'UTF-8').'%'],
            $boolean,
        );
    }

    public static function orWhere(Builder $query, string $column, string $value): Builder
    {
        return self::where($query, $column, $value, 'or');
    }
}
