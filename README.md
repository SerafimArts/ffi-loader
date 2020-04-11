# FFI Loader

## Requirements

- PHP >= 7.4
- ext-ffi

## Installation

Library is available as composer repository and can be installed using the following command in a root of your project.

```sh
$ composer require serafim/ffi-loader
```

## Simple Example

### Define Your Library

```php
use Serafim\FFILoader\Library;
use Serafim\FFILoader\OperatingSystem;
use Serafim\FFILoader\BitDepth;

class Kernel32 extends Library
{
    public function getName() : string
    {
        return 'Example Library';
    }

    public function getHeaders(): string
    {
        return '/path/to/headers.h';
    }

    public function getVersion(string $library): string
    {
        /** @see https://docs.microsoft.com/en-us/windows/win32/api/sysinfoapi/nf-sysinfoapi-getversion */
        $version = \FFI::cdef('extern unsigned long GetVersion();', $library)->GetVersion();
        
        return \vsprintf('%d.%d.%d', [
            $version & 0xff,
            $version >> 8,
            $version < 0x80000000 ? ($version >> 8 & 0xff) : 0,
        ]);
    }

    public function getLibrary(OperatingSystem $os, BitDepth $bits): ?string
    {
        return $os->isWindows() ? 'kernel32.dll' : null;
    }

    public function suggest(OperatingSystem $os, BitDepth $bits): ?string
    {
        return 'Your OS (' . $os . ') does not support this library';
    }
}
```

### Library Loading

```php
use Serafim\FFILoader\Loader;

$loader = new Loader();
$info = $loader->load(new Kernel32());

var_dump(
    $info->bin,         // "kernel32.dll"
    $info->version,     // "10.4700672.0"
    $info->name,        // "kernel32"
    $info->directory,   // "."
);

$ffi = $info->ffi;      // An instance of FFI
```

## Preprocessor

The implementation of the preprocessor is **not completely** equivalent to the 
preprocessor C and only supports a simple set of instructions to speed up parsing.

```php
$pre = new \Serafim\FFILoader\Preprocessor();

$pre->includeFrom('/path/to/includes'); // Add include directory (for `#include` directive)
$pre->define('A');                      // Define "A" as empty string
$pre->define('A', '42');                // Define "A" as "42"
$pre->undef('A');                       // Remove "A" definition

$pre->keepComments = true;              // Save comments in output code (or remove if "false")
$pre->minify = false;                   // Keep extra empty lines (or remove if "true")
$pre->tolerant = true;                  // Ignore unrecognized or bad expressions (throws an errors if "false")
```

### Expressions

- `#if a > b` - greater than
- `#if a < b` - less than expr
- `#if a >= b` - greater or equal expr
- `#if a <= b` - less or equal expr
- `#if a == b` - equal expr
- `#if a != b` - not equal expr

**Types**
- Integer (like `42`)
- Float (like `0.23`)
- Version (special type like `1.0.0`)
- Boolean (`true` or `false`)

```c
#if 0 > 1
    #if true
        // Example
    #endif
#endif
```

### Other Directives

- `#ifdef a` - if defined
- `#ifndef a` - if not defined
- `#define A B` - define "A" as "B"
- `#define A` - define "A" as empty string
- `#undef A` - remove define "A"
- `#include "xxxx"` or `#include <xxxx>` - include other header file

**Library Defines**

- `__[library_name]_version__` - version of loaded library
- `__[library_name]_bin__` - path to binaries (.dll or .so) of loaded library
- `__[library_name]_name__` - name of loaded library

**Environment Defines**
- `__i386__` - x86 OS architecture
- `__amd64__` - x64 OS architecture
- `__windows__`, `__win__`, `__cygwin__`, `__mingw32__` - Windows
- `__win32__` - Windows x86
- `__win64__` - Windows x64
- `__linux__` - Linux
- `__osx__`, `__apple__` or `__macosx__` - Darwin (MacOS)
- `__freebsd__`, `__freebsd_kernel__`, `__dragonfly__`, `__bsdi__` or `__openbsd__` - BSD
- `__sun__`, `__svr4__` or `__solaris__` - Solaris

> Note: All env defines are allowed in `__xxx__`, `__xxx` and `_xxx` format.

```c
#define A 42

#ifdef A
    #ifdef __windows__
        #if __kernel32_version__ > 10.0
            // OK
        #endif
    #endif
#endif
```

