<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

/**
 * Class LibraryInformation
 */
class LibraryInformation
{
    /**
     * @var string
     */
    private const HEADERS_KERNEL32 = __DIR__ . '/../../resources/kernel32.h';

    /**
     * @var string
     */
    private const PHP_ZTS_WARNING =
        'This functionality is not intended to work inside the PHP ZTS ' .
        'version and may cause errors when calling over multiple threads. ' .
        'Please use PHP NTS version instead';

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
     * @var mixed|\FFI|null
     */
    private static ?\FFI $kernel32 = null;

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
        if (\PHP_OS_FAMILY !== 'Windows') {
            return $expr();
        }

        /** @var string $before */
        $before = \getcwd();

        try {
            self::from($this->directory);

            return $expr();
        } finally {
            self::from($before);
        }
    }

    /**
     * @param string $directory
     * @return void
     */
    private static function from(string $directory): void
    {
        if (\ZEND_THREAD_SAFE) {
            @\trigger_error(self::PHP_ZTS_WARNING);

            (self::$kernel32 ?? \FFI::load(self::HEADERS_KERNEL32))
                ->SetDllDirectoryA($directory);

            return;
        }

        \chdir($directory);
    }
}
