<?php

namespace Razu\LaravelHooks;

class Action extends BaseHook
{
    /**
     * Executes all functions attached to a given action hook with the specified arguments.
     *
     * This method will check if there are hooks registered for the given tag. If there are, it will sort them
     * by priority and then execute each registered function with the provided arguments. The execution order is
     * determined by the priority of the hooks, with lower priorities executed first.
     *
     * @param string $tag The name of the action hook to apply.
     * @param mixed ...$args The arguments to pass to the functions attached to the action hook.
     *
     * @return void
     *
     * @throws \Exception If the hook functions are not callable or if any error occurs during the execution of the functions.
     *
     * @see \Razu\LaravelHooks\Action::add_hook() To add a hook for a specific tag.
     */
    public function apply($tag, ...$args)
    {
        if (!isset($this->hooks[$tag])) {
            return;
        }

        if (!isset($this->merged_hooks[$tag])) {
            ksort($this->hooks[$tag]);
            $this->merged_hooks[$tag] = true;
        }

        foreach ($this->hooks[$tag] as $priority_hooks) {
            foreach ($priority_hooks as $hook) {
                call_user_func_array($hook['function'], $args);
            }
        }
    }
}
