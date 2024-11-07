# Laravel Hooks

The filter and action system in Laravel is inspired by WordPress hooks

## About

<b>Actions</b> are code snippets that run at specific points in your program. They don't return any value but provide a way to hook into your existing code without causing interference.

<b>Filters</b> are designed to alter data. They always return a value, typically the modified version of the first parameter, which is the default return behavior.

## More Resources

[Read more about filters](http://www.wpbeginner.com/glossary/filter/)

[Read more about actions](http://www.wpbeginner.com/glossary/action/)

[Read more about filters and actions](https://developer.wordpress.org/plugins/hooks/)


## Installation

1. Install using Composer

```
composer require razu/laravel-hooks
```
**Great!** Now you're ready to use laravel-hooks.

## Usage

#### EVENT

```
do_action( string $tag, mixed $arg )
```

The do_action function is used to trigger all callback functions attached to a specified action hook, $tag.

**Parameters**
- `$tag`
*(string) (Required) – The name of the action hook that will be triggered. This specifies the point in the code where the action occurs.*

- `$arg`
*(mixed) (Optional) – Additional arguments that can be passed to the functions hooked to this action. Defaults to empty if not provided.*

<small>
    By calling <b>*do_action*</b>, you execute all functions linked to the specified <b>*$tag*</b>, passing any optional arguments to each function. This allows custom code to be run at specific points within the application.
<small>

#### LISTEN

```
add_action( string $tag, callable $callback, int $priority = 10, int $accepted_args = 1 )
```

The add_action function associates a callback function with a specified hook ($tag) in a Laravel or WordPress-style hook system. Here’s what each parameter does:

**Parameters**
- `$tag`
*(string) (Required) The name of the hook to which the callback function should be attached.*

- `$callback`
*(callable) (Required) The function to be executed when the hook is triggered.*

- `$priority`
*(int) (Optional) Determines the order in which the functions associated with a particular hook are executed. Lower numbers correspond to earlier execution. Default value: 10*

- `$accepted_args`
*(int) (Optional) The number of arguments the callback function accepts. Default value: 1*

By using <b>*add_action*</b>, you can ensure that your custom functions are executed at the appropriate points in your application’s lifecycle, 
allowing you to extend or modify functionality as needed.

