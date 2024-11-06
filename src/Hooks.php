<?php

namespace Razu\LaravelHooks;

/**
 * Class Hooks
 * 
 * This class provides functionality to manage hooks (actions and filters) 
 * within a Laravel application. It allows adding, removing, and executing hooks,
 * similar to the WordPress hook system.
 */
if (!class_exists('Hooks')){
  class Hooks
  {
    
     /**
     * @var array $filters
     * Stores all registered filters, organized by tag and priority.
     * Filters modify data and are executed in the order of their priority.
     */
    public $filters = [];

     /**
     * @var array $merged_filters
     * Caches merged filters for optimized execution.
     * This helps to reduce processing overhead by storing the final merged filters.
     */
    protected $merged_filters = [];

    /**
     * @var array $actions
     * Stores actions to be triggered, organized by tag and priority.
     * Actions are executed when their respective event is triggered.
     */
    protected $actions = [];

    /**
     * @var array $current_filter
     * Keeps track of the current filter being executed.
     * Useful for debugging and ensuring the correct filter is being processed.
     */
    public $current_filter = [];
        
    /**
     * Constructor
     *
     * Initializes properties for managing filters, actions, and the current filter.
     *
     * @param mixed $args Optional arguments for initializing hooks.
     */
    public function __construct($args = null)
    {
      $this->filters = [];
      $this->merged_filters = [];
      $this->actions = [];
      $this->current_filter = [];
    }

    /**
     * Registers a hook (filter or action) with a specified tag, callback, and priority.
     *
     * This method stores the hook in the `$filters` array (or `$actions` for actions),
     * and clears the merged filters cache to ensure the latest hooks are used.
     *
     * @param string $tag The hook identifier.
     * @param callable $callback The function to be executed for the hook.
     * @param int $priority The execution priority (default is 10).
     * @param int $accepted_args The number of arguments the callback accepts (default is 1).
     * @return bool Returns `true` on successful registration.
     */
    public function add_hook($tag,$callback,$priority = 10, $accepted_args = 1)
    {
        $hookId =  $this->generateHookUniqueId($tag, $callback, $priority);
        $this->filters[$tag][$priority][$hookId] = ['function' => $callback, 'accepted_args' => $accepted_args];
        unset( $this->merged_filters[ $tag ] );
        return true;
    }

    /**
     * Removes a registered hook (filter or action) by tag, callback, and priority.
     *
     * This method checks if the hook exists and removes it from the `$filters` array.
     * If no hooks remain for a given tag and priority, it cleans up the corresponding entries.
     *
     * @param string $tag The hook identifier.
     * @param callable $callbackFunction The function to be removed.
     * @param int $priority The priority of the hook (default is 10).
     * @return bool Returns `true` if the hook was removed, `false` if not found.
     */
    public function remove_hook( $tag, $callbackFunction, $priority = 10)
    {
        $callbackFunction = $this->generateHookUniqueId($tag, $callbackFunction, $priority);

        $hookExists = isset($this->filters[$tag][$priority][$callbackFunction]);

        if ( true === $hookExists) {
            unset($this->filters[$tag][$priority][$callbackFunction]);
            if ( empty($this->filters[$tag][$priority]) ) {
            unset($this->filters[$tag][$priority]);
            }
            unset($this->merged_filters[$tag]);
        }
        return $hookExists;
    }

    /**
     * Removes all hooks (filters or actions) for a specific tag and optional priority.
     *
     * This method removes all hooks registered under a given tag. If a priority is
     * specified, it removes hooks for that specific priority. It also clears the
     * merged filter cache for the tag.
     *
     * @param string $tag The hook identifier.
     * @param int|bool $priority The priority of the hooks to remove (optional).
     *                          If `false`, removes all priorities for the tag.
     * @return bool Always returns `true` after removal.
     */
    public function remove_all_hooks($tag, $priority = false)
    {
        if( isset($this->filters[$tag]) ) {
            if ( false !== $priority && isset($this->filters[$tag][$priority]) ) {
              unset($this->filters[$tag][$priority]);
            } 
            unset($this->filters[$tag]);
          }
    
          if ( isset($this->merged_filters[$tag]) ) {
            unset($this->merged_filters[$tag]);
          }
            
    
          return true;
    }

    /**
     * Checks if a hook (filter or action) is registered for a specific tag and optional callback.
     *
     * This method checks whether a hook exists for the given tag. If a callback is provided,
     * it will also verify if the specific callback is registered. If found, it returns the
     * priority at which the hook is registered. Otherwise, it returns `false`.
     *
     * @param string $tag The hook identifier.
     * @param callable|bool $callback The callback function to check for (optional).
     *                                If `false`, checks for any hook registered under the tag.
     * @return mixed The priority of the hook if found, otherwise `false`.
     */
    public function has_hook($tag, $callback = false)
    {
        $isExist = !empty($this->filters[$tag]);
      
        if ( false === $callback || false == $isExist ) {
            return $isExist;
        }

        if ( !$hookId = $this->generateHookUniqueId($tag, $callback, false) ) {
            return false;
        }

        foreach ( array_keys($this->filters[$tag]) as $priority ) {
            if ( isset($this->filters[$tag][$priority][$hookId]) ) {
            return $priority;
            }
        }
        return false;
    }

    /**
     * Checks if a specific filter is currently being executed.
     *
     * If no filter is provided, this method returns whether any filter is currently being executed.
     * If a filter is provided, it checks if that specific filter is currently being processed.
     *
     * @param string|null $filter The filter to check (optional).
     * @return bool `true` if the filter is being executed, otherwise `false`.
     */
    public function doing_hook( $filter = null )
    {
        if ( null === $filter ) {
            return ! empty( $this->current_filter );
          } 
          return in_array( $filter, $this->current_filter );
    }

    /**
     * Registers a filter hook with a specified tag, callback, and priority.
     *
     * This method is a wrapper for `add_hook`, specifically for registering filter hooks.
     *
     * @param string $tag The filter identifier.
     * @param callable $callback The function to be executed for the filter.
     * @param int $priority The priority of the filter (default is 10).
     * @param int $accepted_args The number of arguments the callback accepts (default is 1).
     * @return bool Returns `true` on successful registration.
     */
    public function add_filter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
      return $this->add_hook($tag, $callback, $priority, $accepted_args);
    }

    /**
     * Removes a registered filter hook by tag, callback, and priority.
     *
     * This method is a wrapper for `remove_hook`, specifically for removing filter hooks.
     *
     * @param string $tag The filter identifier.
     * @param callable $callbackFunction The function to be removed.
     * @param int $priority The priority of the filter (default is 10).
     * @return bool Returns `true` if the filter was removed, `false` if not found.
     */
    public function remove_filter( $tag, $callbackFunction, $priority = 10 )
    {
       return $this->remove_hook($tag,$callbackFunction,$priority);
    }

    /**
     * Removes all registered filter hooks for a specific tag and optional priority.
     *
     * This method is a wrapper for `remove_all_hooks`, specifically for removing all filter hooks.
     *
     * @param string $tag The filter identifier.
     * @param int|bool $priority The priority of the filters to remove (optional).
     *                           If `false`, removes all priorities for the tag.
     * @return bool Always returns `true` after removal.
     */
    public function remove_all_filters($tag, $priority = false)
    {
      return $this->remove_all_hooks($tag,$priority);
    }

    /**
     * Checks if a filter is registered for a specific tag and optional callback.
     *
     * This method is a wrapper for `has_hook`, specifically for checking filter hooks.
     *
     * @param string $tag The filter identifier.
     * @param callable|bool $callback The callback function to check for (optional).
     *                                If `false`, checks for any filter registered under the tag.
     * @return mixed The priority of the filter if found, otherwise `false`.
     */
    public function has_filter($tag, $callback = false)
    {
       return $this->has_hook($tag, $callback);
    }

    /**
     * Applies all registered filters for a specific tag to a given value.
     *
     * This method processes filters registered for a tag and modifies the passed value.
     * It also supports an 'all' filter that applies to all tags, if registered.
     *
     * @param string $tag The filter identifier.
     * @param mixed $value The value to be filtered.
     * @return mixed The filtered value after applying all relevant filters.
     */
    public function apply_filters($tag, $value)
    {
      $args = [];
      
      if ( isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
        $args = func_get_args();
        $this->callAllRegisterHooks($args);
      }

      if ( !isset($this->filters[$tag]) ) {
        if ( isset($this->filters['all']) ) {
          array_pop($this->current_filter);
        }
        return $value;
      }

      if ( !isset($this->filters['all'] )) {
        $this->current_filter[] = $tag;
      }
      
      if ( !isset( $this->merged_filters[$tag] )) {
        ksort($this->filters[$tag]);
        $this->merged_filters[ $tag ] = true;
      }

      reset($this->filters[ $tag ]);

      if (empty($args)) {
        $args = func_get_args();
      }

      do {
        foreach(current($this->filters[$tag]) as $theCurrent ) {
          if ( !is_null($theCurrent['function']) ) {
            $args[1] = $value;
            $value = call_user_func_array($theCurrent['function'], array_slice($args, 1, (int) $theCurrent['accepted_args']));
          }
        }
      } while ( next($this->filters[$tag]) !== false );

      array_pop($this->current_filter);

      return $value;
    }

    /**
     * Applies filters to a value passed by reference, using an array of arguments.
     *
     * This method processes filters registered for a tag, modifying the first argument 
     * of the passed array by reference. It also supports an 'all' filter that applies 
     * to all tags if registered.
     *
     * @param string $tag The filter identifier.
     * @param array $args The array of arguments to be filtered (passed by reference).
     * @return mixed The filtered value (first element of the modified $args array).
     */
    public function apply_filters_ref_array($tag, $args)
    {
      if ( isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
        $all_args = func_get_args();
        $this->callAllRegisterHooks($all_args);
      }

      if ( !isset($this->filters[$tag]) ) {
        if ( isset($this->filters['all']) ) {
          array_pop($this->current_filter);
        }
        return $args[0];
      }

      if ( !isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
      }
      
      if ( !isset( $this->merged_filters[ $tag ] ) ) {
        ksort($this->filters[$tag]);
        $this->merged_filters[$tag] = true;
      }

      reset($this->filters[$tag]);

      do {
        foreach( (array) current($this->filters[$tag]) as $theCurrent ) {
          if ( !is_null($theCurrent['function']) ) {
            $args[0] = call_user_func_array($theCurrent['function'], array_slice($args, 0, (int) $theCurrent['accepted_args']));
          }
        }
      } while ( next($this->filters[$tag]) !== false );

      array_pop($this->current_filter);

      return $args[0];
    }

    /**
     * Registers an action hook with a specific tag and callback function.
     *
     * This method is a wrapper for `add_hook`, specifically for registering action hooks.
     *
     * @param string $tag The action identifier.
     * @param callable $callback The function to be called when the action is triggered.
     * @param int $priority The priority of the action (default is 10).
     * @param int $accepted_args The number of arguments the callback accepts (default is 1).
     * @return bool Always returns `true` after the action is registered.
     */
    public function add_action($tag, $callback, $priority = 10, $accepted_args = 1)
    {
      return $this->add_hook($tag, $callback, $priority, $accepted_args);
    }

    /**
     * Checks if an action hook is registered for a specific tag.
     *
     * This method is a wrapper for `has_hook` to check for action hooks.
     *
     * @param string $tag The action identifier.
     * @param callable|false $callbackToCheck The callback function to check (optional).
     * @return mixed The priority if the action is registered, `false` otherwise.
     */
    public function has_action($tag, $callbackToCheck = false)
    {
      return $this->has_hook($tag, $callbackToCheck);
    }

    /**
     * Removes a registered action hook.
     *
     * This method is a wrapper for `remove_hook` to remove action hooks.
     *
     * @param string $tag The action identifier.
     * @param callable $callback The callback function to remove.
     * @param int $priority The priority of the action (default is 10).
     * @return bool Returns `true` if the action was removed, `false` otherwise.
     */
    public function remove_action( $tag, $callback, $priority = 10 )
    {
      return $this->remove_hook($tag,$callback,$priority);
    }

    /**
     * Removes all action hooks for a specific tag.
     *
     * This method is a wrapper for `remove_all_hooks` to remove all actions 
     * registered for a given tag, optionally filtered by priority.
     *
     * @param string $tag The action identifier.
     * @param int|bool $priority The priority of the actions to remove (optional).
     * @return bool Always returns `true` after removal.
     */
    public function remove_all_actions($tag, $priority = false)
    {
      return $this->remove_all_hooks($tag,$priority);
    }

    /**
     * Executes the registered action hooks for a given tag.
     *
     * This method triggers all functions hooked to the specified action tag. 
     * It also allows passing additional arguments to the hooks.
     *
     * @param string $tag The action identifier.
     * @param mixed $arg The arguments to pass to the hooked functions.
     * @return void
     */
    public function do_action($tag, $arg = '')
    {
      if ( ! isset($this->actions) ) {
        $this->actions = array();
      }

      if ( ! isset($this->actions[$tag]) ) {
        $this->actions[$tag] = 1;
      } else {
        ++$this->actions[$tag];
      }
      
      if ( isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
        $all_args = func_get_args();
        $this->callAllRegisterHooks($all_args);
      }

      if ( !isset($this->filters[$tag]) ) {
        if ( isset($this->filters['all']) ) {
          array_pop($this->current_filter);
        }
        return;
      }

      if ( !isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
      }

      $args = [];
      if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) {
        $args[] =& $arg[0];
      } else {
        $args[] = $arg;
      }

      for ( $index = 2; $index < func_num_args(); $index++ ) {
        $args[] = func_get_arg($index);
      }
      
      if ( !isset( $this->merged_filters[ $tag ] ) ) {
        ksort($this->filters[$tag]);
        $this->merged_filters[ $tag ] = true;
      }

      reset( $this->filters[ $tag ] );

      do {
        foreach ( (array) current($this->filters[$tag]) as $the_ ) {
          if ( !is_null($the_['function']) ) {
            call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
          }
        }
      } while ( next($this->filters[$tag]) !== false );

      array_pop($this->current_filter);
    }

    /**
     * Executes the registered action hooks for a given tag with arguments passed by reference.
     *
     * This method triggers all functions hooked to the specified action tag and passes the arguments by reference to the hooked functions. 
     * It also allows additional arguments to be passed and modifies them directly.
     *
     * @param string $tag The action identifier.
     * @param array $args The arguments to pass by reference to the hooked functions.
     * @return void
     */
    public function do_action_ref_array($tag, $args)
    {
      if ( ! isset($this->actions) ) {
        $this->actions = array();
      }

      if ( ! isset($this->actions[$tag]) ) {
        $this->actions[$tag] = 1;
      } else {
        ++$this->actions[$tag];
      }

      if ( isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
        $all_args = func_get_args();
        $this->callAllRegisterHooks($all_args);
      }

      if ( !isset($this->filters[$tag]) ) {
        if ( isset($this->filters['all']) ) {
          array_pop($this->current_filter);
        }
        return;
      }

      if ( !isset($this->filters['all']) ) {
        $this->current_filter[] = $tag;
      }
      
      if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($this->filters[$tag]);
        $merged_filters[ $tag ] = true;
      }

      reset( $this->filters[ $tag ] );

      do {
        foreach( (array) current($this->filters[$tag]) as $theCurrent ) {
          if ( !is_null($theCurrent['function']) ) {
            call_user_func_array($theCurrent['function'], array_slice($args, 0, (int) $theCurrent['accepted_args']));
          }
        }
      } while ( next($this->filters[$tag]) !== false );

      array_pop($this->current_filter);
    }

    /**
     * Checks how many times a specific action has been triggered.
     *
     * This method returns the number of times the action identified by the given tag has been executed. 
     * If the action has not been triggered, it returns 0.
     *
     * @param string $tag The action identifier.
     * @return int The number of times the action has been triggered.
     */
    public function did_action($tag)
    {
      if ( ! isset( $this->actions ) || ! isset( $this->actions[$tag] ) ) {
        return 0;
      }

      return $this->actions[$tag];
    }

    /**
     * HELPERS
     */

    /**
     * Gets the current filter being executed.
     *
     * This method returns the most recently added filter tag from the current filter stack.
     *
     * @return string|null The current filter tag or null if no filter is being executed.
     */
    public function current_filter()
    {
      return end( $this->current_filter );
    }

    /**
     * Gets the current action being executed.
     *
     * This method returns the most recently added action tag, which is essentially the current filter.
     * It is an alias of the `current_filter()` method.
     *
     * @return string|null The current action tag or null if no action is being executed.
     */
    function current_action()
    {
      return $this->current_filter();
    }
    
    /**
     * Checks if a specific filter is being applied.
     *
     * This method checks whether the given filter is currently being executed.
     * If no filter is provided, it returns whether any filter is being applied.
     *
     * @param string|null $filter The name of the filter to check (optional).
     * 
     * @return bool True if the specified filter is being executed, otherwise false.
     */
    function doing_filter( $filter = null )
    {
        return $this->doing_hook( $filter );
    }
    
    /**
     * Checks if a specific action is being performed.
     *
     * This method checks whether the given action is currently being executed.
     * If no action is provided, it returns whether any action is being performed.
     *
     * @param string|null $action The name of the action to check (optional).
     * 
     * @return bool True if the specified action is being executed, otherwise false.
     */
    public function doing_action( $action = null )
    {
      return $this->doing_hook( $action );
    }
    
    /**
     * Generates a unique ID for a hook function.
     *
     * This method generates a unique identifier for a hook callback function,
     * based on the function type (string, object, or array). It ensures that 
     * the same callback for the same tag and priority gets a consistent identifier.
     *
     * @param string $tag The name of the hook tag.
     * @param mixed $function The callback function (could be a string, object, or array).
     * @param int $priority The priority at which the function is hooked (optional).
     * 
     * @return string|false The unique identifier for the function or false if it cannot be generated.
     */
    private function generateHookUniqueId($tag, $function, $priority)
    {
      static $filter_id_count = 0;

      if ( is_string($function) ) {
        return $function;
      }

      if ( is_object($function) ) {
        $function = array( $function, '' );
      } else {
        $function = (array) $function;
      }

      if (is_object($function[0]) ) {
        if ( function_exists('spl_object_hash') ) {
          return spl_object_hash($function[0]) . $function[1];
        } else {
          $obj_idx = get_class($function[0]).$function[1];
          if ( !isset($function[0]->filter_id) ) {
            if ( false === $priority ) {
              return false;
            }
            $obj_idx .= isset($this->filters[$tag][$priority]) ? count((array)$this->filters[$tag][$priority]) : $filter_id_count;
            $function[0]->filter_id = $filter_id_count;
            ++$filter_id_count;
          } else {
            $obj_idx .= $function[0]->filter_id;
          }

          return $obj_idx;
        }
      } else if ( is_string($function[0]) ) {
        return $function[0].$function[1];
      }
    }

    /**
     * Calls all hooks registered under the 'all' tag.
     *
     * This method iterates through all hooks registered under the 'all' tag and 
     * executes their associated callback functions with the provided arguments. 
     * It ensures that each hook is invoked in the order they were registered.
     *
     * @param array $args The arguments to be passed to the hook callbacks.
     */
    public function callAllRegisterHooks($args) {
      reset( $this->filters['all'] );
      do {
        foreach( (array) current($this->filters['all']) as $theCurrent ) {
          if ( !is_null($theCurrent['function']) ) {
            call_user_func_array($theCurrent['function'], $args);
          }
        }
      } while ( next($this->filters['all']) !== false );
    }
  }
}
