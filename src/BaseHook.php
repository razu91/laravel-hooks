<?php

namespace Razu\LaravelHooks;

abstract class BaseHook
{
    protected $hooks = [];
    protected $merged_hooks = [];
    protected $current_hooks = [];

    public function __construct()
    {
        $this->hooks = [];
        $this->merged_hooks = [];
        $this->current_hooks = [];
    }

    // Common method for adding hooks
    public function add_hook($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        $idx = $this->hookUniqueId($tag, $callback, $priority);
        $this->hooks[$tag][$priority][$idx] = ['function' => $callback, 'accepted_args' => $accepted_args];
        unset($this->merged_hooks[$tag]);
    }

    // Common method to remove hooks
    public function remove_hook($tag, $callback, $priority = 10)
    {
        $idx = $this->hookUniqueId($tag, $callback, $priority);
        if (isset($this->hooks[$tag][$priority][$idx])) {
            unset($this->hooks[$tag][$priority][$idx]);
            if (empty($this->hooks[$tag][$priority])) {
                unset($this->hooks[$tag][$priority]);
            }
            unset($this->merged_hooks[$tag]);
        }
    }

    // Generate unique IDs for hooks
    protected function hookUniqueId($tag, $function, $priority)
    {
        return md5($tag . serialize($function) . $priority);
    }

    abstract public function apply($tag, $value);
}
