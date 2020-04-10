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
