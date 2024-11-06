# Laravel Hooks

The filter and action system in Laravel is inspired by WordPress hooks

## About

<b>Actions</b> are code snippets that run at specific points in your program. They don't return any value but provide a way to hook into your existing code without causing interference.

<b>Filters</b> are designed to alter data. They always return a value, typically the modified version of the first parameter, which is the default return behavior.

## Learning Resources

[Read more about filters](http://www.wpbeginner.com/glossary/filter/)

[Read more about actions](http://www.wpbeginner.com/glossary/action/)

[Read more about filters and actions](https://developer.wordpress.org/plugins/hooks/)


## Installation

1. Install using Composer

```
composer require razu/laravel-hooks
```
**Great!** Now you're ready to use laravel-hooks.