<?php

namespace Razu\LaravelHooks;

class Action extends BaseHook
{
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
