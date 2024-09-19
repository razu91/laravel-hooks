<?php

namespace Razu\LaravelHooks;

class Filter extends BaseHook
{
    public function apply($tag, $value)
    {
        if (!isset($this->hooks[$tag])) {
            return $value;
        }

        if (!isset($this->merged_hooks[$tag])) {
            ksort($this->hooks[$tag]);
            $this->merged_hooks[$tag] = true;
        }

        foreach ($this->hooks[$tag] as $priority_hooks) {
            foreach ($priority_hooks as $hook) {
                $value = call_user_func_array($hook['function'], [$value]);
            }
        }

        return $value;
    }
}
