<?php

namespace Razu\LaravelHooks;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class HooksServiceProvider extends ServiceProvider
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
        // Register action and filter separately
        $this->app->singleton('hooks.action', function ($app) {
            return new Action();
        });

        $this->app->singleton('hooks.filter', function ($app) {
            return new Filter();
        });
    }
}
