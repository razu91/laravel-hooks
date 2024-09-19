<?php

if (!function_exists('hook')) {
    /**
     * instance of Hooks class (action or filter)
     *
     * @param string $type
     * @return object
     */
    function hook($type = 'filter')
    {
        return app('hooks.' . $type);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Add a filter
     *
     * @param string $tag
     * @param callback $callback
     * @param integer $priority
     * @param integer $accepted_args
     * @return boolean
     */
    function add_filter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        return hook('filter')->add_hook($tag, getHookCallback($callback), $priority, $accepted_args);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook.
     *
     * @param string $tag
     * @param mixed $value
     * @return mixed
     */
    function apply_filters($tag, $value)
    {
        return hook('filter')->apply($tag, $value);
    }
}

if (!function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook
     *
     * @param string $tag
     * @param mixed $arg
     * @return void
     */
    function do_action($tag, $arg = '')
    {
        hook('action')->apply($tag, $arg);
    }
}

if (!function_exists('add_action')) {
    /**
     * Add an action for do_action
     *
     * @param string $tag
     * @param callback $callback
     * @param integer $priority
     * @param integer $accepted_args
     * @return void
     */
    function add_action($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        return hook('action')->add_hook($tag, getHookCallback($callback), $priority, $accepted_args);
    }
}

if (!function_exists('getHookCallback')) {
    /**
     * Get callback function
     *
     * @param mixed $callback
     * @return callable
     * @throws \Exception
     */
    function getHookCallback($callback)
    {
        if (is_string($callback) && strpos($callback, '@')) {
            $callback = explode('@', $callback);
            return [app('\\' . $callback[0]), $callback[1]];
        }

        if (is_string($callback)) {
            return [app('\\' . $callback), 'handle'];
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
