<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Razu\LaravelHooks\HookServiceProvider;
use Tests\TestCase;

class HookFeatureTest extends TestCase
{
    /**
     * Register package service providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [HookServiceProvider::class];
    }

    /**
     * Test Blade directive for executing actions.
     *
     * Verifies that the custom Blade directive `@doAction` correctly executes
     * the specified action and outputs the expected result.
     *
     * @return void
     */
    public function test_blade_directive_for_do_action()
    {
        $this->app['hooks']->add_action('my_action',function(){
            echo 'Action executed';
        },10,0);

        $output = Blade::compileString('@doAction(\'my_action\')');
        $this->expectOutputString('Action executed');
        eval('?>' . $output);
    }

    /**
     * Test adding and executing hook aliases (filters and actions).
     *
     * Verifies that hooks can be added and executed correctly. The test checks
     * if a filter modifies the input value and if an action executes and outputs
     * the expected result.
     *
     * @return void
     */
    public function test_hook_function_aliases()
    {
        $this->app['hooks']->add_filter('my_filter', function($value){
            return $value . ' filtered';
        },10, 1);

        $this->assertEquals('Hello World filtered', apply_filters('my_filter','Hello World'));

        $this->app['hooks']->add_action('my_action', function(){
            echo 'Action executed';
        }, 10, 0);

        ob_start();
        do_action('my_action');
        $output = ob_get_clean();
        $this->assertEquals('Action executed', $output);
    }
}
