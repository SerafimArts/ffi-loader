# FFI Loader

## Requirements

- PHP >= 7.4
- ext-ffi

## Installation

Library is available as composer repository and can be installed using the following command in a root of your project.

```sh
$ composer require serafim/ffi-loader
```

## Usage

First, let's define your library. Some environment parameters are already determined automatically, 
so the minimal declaration will look like this.

```php
use Serafim\FFILoader\Library;

class ExampleLibrary extends Library
{
    public function getBinary(): ?string
    {
        return 'kernel32.dll';
    }

    public function getHeaders(): string
    {
         return <<<'clang'
            extern unsigned long GetVersion();
        clang;
    }
}
```

After defining the library, just load it.

```php
use Serafim\FFILoader\Loader;

$ffi = Loader::load(ExampleLibrary::class);
```

### Preloading

Define the following code in your preload file.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Serafim\FFILoader\Loader;

Loader::load(ExampleLibrary::class);
```

Then you can use it:

```php
$ffi = \FFI::scope(ExampleLibrary::class);
```

### Custom Scope Name

To determine custom FFI scope name, you need to add the `getName(): string`
method to your library.

```php
use Serafim\FFILoader\Library;class ExampleLibrary extends Library
{
    public function getName(): string
    {
       return 'kernel32-version';
    }

    // ... other methods ...

}

// Scope Usage

$ffi = \FFI::scope('kernel32-version');
```

### Custom Library Directory

Some libraries may load other dependencies during initialization. You can manually change this directory using
the `chdir()` function or with
[this code](https://www.php.net/manual/en/function.chdir.php#125457) in the case of working in a PHP ZTS environment.

However, the `Loader` is already performing these operations, you just need to define the desired working directory.

```php
use Serafim\FFILoader\Library;class ExampleLibrary extends Library
{
    public function getDirectory(): string
    {
       return __DIR__ . '/bin';
    }

    // ... other methods ...

}
```

### Error Handling

In some cases, it may be necessary to inform the user about the solution of the error during the incorrect loading of
the library. In this case, add a method that describes how to solve the problem.

```php
use Serafim\FFILoader\Library;class ExampleLibrary extends Library
{
    public function getSuggestion(): string
    {
        return \PHP_OS_FAMILY === 'Windows'
            ? 'Something went wrong'
            : 'Your OS does not support this library'
        ;
    }

    // ... other methods ...

}
```

### Preprocessing

The C language contains preprocessing of the source code before it goes directly to the compiler. The library
already [contains a subset](https://github.com/SerafimArts/FFI-Preprocessor) of the
standard ([ISO/IEC 9899:TC2](http://www.open-std.org/jtc1/sc22/wg14/www/docs/n1124.pdf)) preprocessor.

You can add your own directives that are required to build your headers.

```php
use Serafim\FFILoader\Library;class ExampleLibrary extends Library
{
    public function getDirectives(): iterable
    {
        yield 'SOME' => 42;
    }

    // ... other methods ...

}
```

In this case, the source code of headers can use this directive.

```cpp
#if SOME == 42
    #warning directive contains value 42
#else
    #warning directive DOES NOT contain value 42
#endif
```

In addition, you can override the values of directives directly during the loading of the library.

```php
<?php

use Serafim\FFILoader\Loader;

$ffi = Loader::load(ExampleLibrary::class, [
    'SOME' => 23,
]);
```
