<?php namespace App\Illuminate\Database;

use Closure;
use Illuminate\Database\MySqlConnection as BaseConnection;
use App\Illuminate\Database\Query\Builder as QueryBuilder;

class MySqlConnection extends BaseConnection
{
    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    protected function run($query, $bindings, Closure $callback)
    {
        $query = str_ireplace('isnull(', 'IFNULL(', $query);
        $query = str_ireplace('STRING_AGG(', 'group_concat(', $query);
        $query = str_ireplace('[', '`', $query);
        $query = str_ireplace(']', '`', $query);
        $query = str_ireplace('--', '#', $query);

        return parent::run($query, $bindings, $callback);
    }
}
