<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Database\Connection;
use App\Support\EventDispatcher;
use App\Illuminate\Database\MySqlConnection;
use App\Illuminate\Database\SqlsrvConnection;
use Illuminate\Database\Events\StatementPrepared;

use Illuminate\Pagination\Paginator;

use Event;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });

        Connection::resolverFor('sqlsrv', function ($connection, $database, $prefix, $config) {
            return new SqlsrvConnection($connection, $database, $prefix, $config);
        });

        $this->app->singleton('dispatcher', function ($app, $deps) {
            return new EventDispatcher();
        });
        
        // 开发环境开启ide辅助插件
        if ($this->app->environment() == 'development') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require app_path('gdoo.php');

        Paginator::defaultView('vendor/pagination/gdoo');
        Event::listen(StatementPrepared::class, function ($event) {
            $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
        });
    }
}
