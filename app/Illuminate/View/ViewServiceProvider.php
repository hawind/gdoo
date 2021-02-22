<?php namespace App\Illuminate\View;

use Illuminate\View\Engines\CompilerEngine;
use App\Illuminate\View\Compilers\BladeCompiler;

use Illuminate\View\ViewServiceProvider as BaseViewServiceProvider;

class ViewServiceProvider extends BaseViewServiceProvider
{
    public function registerBladeCompiler()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new BladeCompiler($app['files'], $app['config']['view.compiled']), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
        
        /*
        $this->app->singleton('blade.compiler', function () {
            return new BladeCompiler(
                $this->app['files'],
                $this->app['config']['view.compiled']
            );
        });

        $resolver->register('blade', function () {
            return new CompilerEngine($this->app['blade.compiler']);
        });
        */
    }
}
