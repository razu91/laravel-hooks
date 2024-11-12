<?php

namespace Tests\Unit;


use Razu\LaravelHooks\Hooks;
use Tests\TestCase;


class HooksTest extends TestCase
{
    protected $hooks;

    /**
     * Initialize the test environment.
     *
     * Sets up a fresh instance of the Hooks class for each test,
     * ensuring test isolation.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->hooks = new Hooks();
    }

    /**
     * Test adding and removing filters.
     *
     * Verifies that filters can be added and removed correctly,
     * checking that a filter's priority is set on addition and
     * ensuring its removal when requested.
     *
     * @return void
     */
    public function test_adding_and_removing_filters(): void
    {
        // Test adding a filter
        $this->assertTrue($this->hooks->add_filter('my_filter', 'filterCallback', 10, 1));
        $this->assertEquals(10, $this->hooks->has_filter('my_filter','filterCallback'));

        // Test removing a filter
        $this->assertTrue($this->hooks->remove_filter('my_filter','filterCallback',10));
        $this->assertFalse($this->hooks->has_filter('my_filter','filterCallback'));
    }

    /**
     * Test applying filters.
     *
     * Verifies that a filter modifies a given value as expected.
     * Adds a filter to append ' filtered' to the input string,
     * then applies the filter and checks the result.
     *
     * @return void
     */
    public function test_applying_filters()
    {
        $this->hooks->add_filter('my_filter',function($value){
            return $value . ' filtered';
        },10,1);

        $this->assertEquals('Hello World filtered', $this->hooks->apply_filters('my_filter','Hello World'));
    }

    /**
     * Test adding and removing actions.
     *
     * Confirms that actions can be added with a specified priority and
     * removed as expected. Verifies that the action's priority is set on
     * addition and that it no longer exists after removal.
     *
     * @return void
     */
    public function test_adding_and_removing_actions()
    {
        // Test adding an action
        $this->assertTrue($this->hooks->add_action('my_action','actionCallback', 10, 1));
        $this->assertEquals(10, $this->hooks->has_action('my_action','actionCallback'));

        // Test removing an action
        $this->assertTrue($this->hooks->remove_action('my_action','actionCallback',10));
        $this->assertFalse($this->hooks->has_action('my_action','actionCallback'));
    }

    /**
     * Test executing an action.
     *
     * Verifies that an action is executed when triggered. Sets a flag within
     * the action callback, then checks if the action was properly executed
     * by asserting the action count.
     *
     * @return void
     */
    public function test_doing_action()
    {
        $actionExecuted = false;
        $this->hooks->add_action('my_action',function() use (&$actionExecuted){
            $actionExecuted = true;
        },10,0);

        $this->hooks->do_action('my_action');

        $this->assertEquals(1,$this->hooks->did_action('my_action'));
    }

    /**
     * Test removing all hooks.
     *
     * Verifies that all filters and actions associated with a hook can be
     * removed correctly. It checks that after removal, no filters or actions
     * are left for the specified hook.
     *
     * @return void
     */
    public function test_remove_all_hooks()
    {
        $this->hooks->add_filter('my_filter','filterCallback',10,1);
        $this->hooks->add_action('my_action','actionCallback',10,1);

        $this->hooks->remove_all_filters('my_filter');
        $this->assertFalse($this->hooks->has_filter('my_filter','filterCallback'));

        $this->hooks->remove_all_actions('my_action');
        $this->assertFalse($this->hooks->has_action('my_action','actionCallback'));
    }
}
