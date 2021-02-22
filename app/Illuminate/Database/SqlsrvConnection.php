<?php namespace App\Illuminate\Database;

use Illuminate\Database\SqlServerConnection as BaseConnection;
use App\Illuminate\Database\Query\Builder as QueryBuilder;

class SqlsrvConnection extends BaseConnection
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
}
