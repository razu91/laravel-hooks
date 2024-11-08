# Laravel Hooks

The filter and action system in Laravel is inspired by WordPress hooks

## About

<b>Actions</b> are code snippets that run at specific points in your program. They don't return any value but provide a way to hook into your existing code without causing interference.

<b>Filters</b> are designed to alter data. They always return a value, typically the modified version of the first parameter, which is the default return behavior.

<b>Notes:</b> This works similarly to the `Observer design pattern`, or the `Publisher/Subscriber pattern`, which is also related to `event-driven architecture`.

## More Resources

[Read more about filters](http://www.wpbeginner.com/glossary/filter/)

[Read more about actions](http://www.wpbeginner.com/glossary/action/)

[Read more about filters and actions](https://developer.wordpress.org/plugins/hooks/)


## Installation

1. Install using Composer

```bash
composer require razu/laravel-hooks
```
**Great!** Now you're ready to use laravel-hooks.

## ACTION

#### EVENT

```php
do_action( string $tag, mixed $arg )
```

The *<b>do_action</b>* function is used to trigger all callback functions attached to a specified action hook *<b>($tag)</b>*.

**Parameters**
- `$tag`
*(string) (Required) – The name of the action hook that will be triggered. This specifies the point in the code where the action occurs.*

- `$arg`
*(mixed) (Optional) – Additional arguments that can be passed to the functions hooked to this action. Defaults to empty if not provided.*

<small>
    By calling <b><i>do_action</i></b>, you execute all functions linked to the specified hook <b><i>($tag)</i></b>, passing any optional arguments to each function. This allows custom code to be run at specific points within the application.
</small>

#### LISTENER

```php
add_action( string $tag, callable $callback, int $priority = 10, int $accepted_args = 1 )
```

The *<b>add_action</b>* function associates a callback function with a specified hook *<b>($tag)</b>* in a Laravel or WordPress-style hook system. Here’s what each parameter does:

**Parameters**
- `$tag`
*(string) (Required) The name of the hook to which the callback function should be attached.*

- `$callback`
*(callable) (Required) The function to be executed when the hook is triggered.*

- `$priority`
*(int) (Optional) Determines the order in which the functions associated with a particular hook are executed. Lower numbers correspond to earlier execution. Default value: 10*

- `$accepted_args`
*(int) (Optional) The number of arguments the callback function accepts. Default value: 1*

By using *<b>add_action</b>*, you can ensure that your custom functions are executed at the appropriate points in your application’s lifecycle, 
allowing you to extend or modify functionality as needed.

## FILTER

#### EVENT
```php
apply_filters( string $tag, mixed $value )
```

The *<b>apply_filters</b>* function calls all functions attached to a specific filter hook *<b>($tag)</b>*. This allows you to modify a value by passing it through multiple filters. 

**Parameters**
- `$tag`
*(string) (Required) – The name of the filter hook to trigger. This identifies the specific filter to apply.*
- `$value`
*(mixed) (Required) – The value to be filtered. Each function attached to the hook can modify and return this value.*

When *<b>apply_filters</b>* is called, it sequentially runs all functions associated with hook *<b>($tag)</b>*, passing *<b>$value</b>* through each one. This allows custom modifications to be made to the value at specific points in the application.

#### LISTENER

```php
add_filter( string $tag, callable $callback, int $priority = 10, int $accepted_args = 1 )
```

The *<b>add_filter</b>* function allows you to modify various types of internal data at runtime by attaching callback functions to specific filter hooks.

**Parameters**

-`$tag`
*(string) (Required) – The name of the filter to which the callback will be added. This specifies the specific filter hook where data modification should occur.*

-`$callback`
*(callable) (Required) – The callback function to be executed when the filter is applied. This function should process and potentially modify the filtered data.*

-`$priority`
*(int) (Optional) – Specifies the order in which functions associated with this filter will execute. Lower numbers correspond to earlier execution. Functions with the same priority are executed in the order they were added. The default is 10.*

- `$accepted_args`
*(int) (Optional) – The number of arguments the callback function accepts. The default is 1.*

Using *<b>add_filter</b>*, you can ensure that specific functions modify data when the designated filter is applied, allowing for flexible data processing throughout the application.

### Here’s how to pass a callback function:

The callback can be specified in various ways, including:
- An anonymous function.
- A string referring to a class and method within the application, like `MyNamespace\Http\Listener@myHookListener`.
- An array format, such as `[$object, 'method']`.
- A globally defined function, such as `global_function`.


#### Examples

- Using an anonymous function:
```php
add_action('user_registered', function($user) {
    $user->sendEmailVerificationCode();
}, 20, 1);
```

- Using a class method reference as a string:
```php
add_action('user_registered', 'OurNamespace\Http\OurClass@ourMethod', 20, 1);
```

- Using an array callback:
```php
add_action('user_registered', [$object, 'ourMethod'], 20, 1);
```


## Usages
### Actions
Anywhere in your code, you can define a new action like this:

```php
do_action('user_registered', $user);
```

Here, `user_registered` is the action name, which will be used later when the action is being listened to. 
The `$user` parameter will be available whenever you listen for the action. This parameter can represent any relevant data.

To listen for your actions, attach listeners at the appropriate points. Another good approach is to add these in the `boot()` method of your `AppServiceProvider`.

For example, if you want to hook into the above action, you could do the following:

```php
add_action('user_registered', function($user) {
    $user->sendEmailVerificationCode();
}, 10, 1);
```

The first argument must be the name of the action. The second argument can be closures, callbacks, or anonymous functions. 
The third argument specifies the order in which the functions associated with a particular action are executed. 
Lower numbers correspond to earlier execution, and functions with the same priority are executed in the order in which they were added to the action. 
The default value is 10. The fourth argument specifies the number of arguments the function accepts, with the default value being 1.


### Filters

Filters must always have data coming in and going out to ensure it reaches the browser for output.
Your content might pass through multiple filters before finally being displayed. 
In contrast, actions—though similar to filters—do not require any data to be returned. However, 
data can still be passed through actions if needed.

Filters are functions in Laravel applications that allow data to be passed through and modified.
They enable developers to adjust the default behavior of specific functions.

Here’s an example of how a filter can be used in a application.

In the `Product.php` model, we have a `getAvailable` method that builds a query to fetch all available products:

```php
class Product extends Model
{
    public function getAvailable()
    {
        return Product::where('available_at', '<=', now());
    }
}
```

Using a filter, we can modify this query dynamically:

```php
class Product extends Model
{
    public function getAvailable()
    {
        return apply_filters('products_available', Product::where('available_at', '<=', now()));
    }
}
```

Now, in the entry point of the application, such as in a module or plugin, you can modify this product availability query.

In the service provider of the module or plugin (ideally within the boot method), we'll register a listener for the filter.

```php
class ModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Registering a filter to modify the query for available products
        add_filter('products_available', function($query) {
            return $query->where('status', 'active');
        });
    }
}
```