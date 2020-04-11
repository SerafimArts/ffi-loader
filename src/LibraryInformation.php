<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use Serafim\FFILoader\Support\LoadLibrary;

/**
 * Class LibraryInformation
 */
class LibraryInformation
{
    /**
     * @var string
     */
    public string $bin;

    /**
     * @var string
     */
    public string $version;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $directory;

    /**
     * @var \FFI
     */
    public \FFI $ffi;

    /**
     * LibraryInformation constructor.
     *
     * @param string $bin
     * @param string $version
     * @param string $name
     * @param \FFI $ffi
     */
    public function __construct(string $bin, string $version, string $name, \FFI $ffi)
    {
        $this->bin = $bin;
        $this->version = $version;
        $this->name = $name;
        $this->ffi = $ffi;
        $this->directory = \dirname($this->bin);
    }

    /**
     * @param callable $expr
     * @return mixed
     */
    public function chdir(callable $expr)
    {
        return LoadLibrary::chdir($this->directory, $expr);
    }
}
