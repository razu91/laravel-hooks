<?php

if (!function_exists('hook')) {

    /**
     * Get the instance of the 'hooks' service from the Laravel service container.
     *
     * @return object
     */
    function hook()
    {
        return app('hooks');
    }
}

if (!function_exists('add_filter')) {

    /**
     * Adds a callback to a specific filter hook.
     *
     * @param string $tag The name or identifier of the filter hook.
     * @param callable $callback The callback function to be executed when the filter is triggered. This can be a string (function name), an array (class method), or a closure.
     * @param int $priority (Optional) The priority at which the callback function should be executed. Defaults to 10. Higher numbers correspond to later execution.
     * @param int $accepted_args (Optional) The number of arguments the callback should accept. Defaults to 1.
     *
     * @return boolean The result of the `add_filter` method from the hook service, confirming if the filter was added.
     */
    function add_filter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        return hook()->add_filter($tag, getHookCallback($callback), $priority, $accepted_args);
    }
}

if (!function_exists('apply_filters')) {

    /**
     * Applies all filters to a given value based on the provided filter tag.
     *
     * @param string $tag The name or identifier of the filter hook to be applied.
     * @param mixed $value The value to be filtered. This is the initial value that will be passed through the filter callbacks.
     *
     * @return mixed The filtered value after all the filters have been applied.
     */
    function apply_filters($tag, $value)
    {
        return hook()->apply_filters($tag, $value);
    }
}

if (!function_exists('do_action')) {

    /**
     * Triggers the specified action hook and passes arguments to the attached callbacks.
     *
     * @param string $tag The name of the action hook to be triggered.
     * @param mixed $arg The argument to be passed to the callback functions. It can be a single value or an array of values.
     *                    Defaults to an empty string if no argument is provided.
     * @return void
     */
    function do_action($tag, $arg = '')
    {
        hook()->do_action($tag, $arg);
    }
}

if (!function_exists('add_action')) {

    /**
     * Registers a callback function to be executed when a specific action hook is triggered.
     *
     * @param string $tag The name of the action hook to which the callback is attached.
     * @param callable $callback The callback function to be executed when the action is triggered.
     *                            The callback can be a string (function name), an array (object method), or a Closure.
     * @param int $priority The order in which the callback should be executed relative to other callbacks for the same hook.
     *                      Lower numbers correspond to earlier execution. Defaults to 10.
     * @param int $accepted_args The number of arguments the callback can accept. Defaults to 1.
     *
     * @return mixed The result of the `add_action` method from the hooks service.
     * @return void
     */
    function add_action($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        return hook()->add_action($tag, getHookCallback($callback), $priority, $accepted_args);
    }
}

if (!function_exists('getHookCallback')) {

    /**
     * Resolves and returns the appropriate callable for the provided callback.
     *
     * @param mixed $callback The callback to be resolved. 
     *
     * @return callable A resolved callable that can be used with hooks, which can be a method, function, or closure.
     *
     * @throws \Exception If the callback is not a valid callable.
     */
    function getHookCallback($callback)
    {
        if (is_string($callback) && strpos($callback, '@')) {
            $callback = explode('@', $callback);
            return [app('\\'.$callback[0]), $callback[1]];
        } 
        
        if (is_string($callback)) {
            return [app('\\'.$callback), 'handle'];
        } 
        
        if (is_callable($callback)) {
            return $callback;
        } 
        
        if (is_array($callback)) {
            return $callback;
        }

        throw new \Exception($callback . ' is not a Callable', 1);
    }
}