<?php

namespace Razu\LaravelHooks;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Razu\LaravelHooks\Hooks;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Blade::directive('doAction', function ($expression) {
            $expressions = explode(',', $expression);
            $name = $expressions[0];
            $arg = $expressions[1] ?? "''";
            return "<?php echo do_action($name, $arg); ?>";
        });
    }

    /**
     * Register all directives.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hooks', function ($app) {
            return new Hooks();
        });
    }
}