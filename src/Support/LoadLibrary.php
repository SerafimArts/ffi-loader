<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Support;

/**
 * Class LoadLibrary
 */
final class LoadLibrary
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
     * @param string $directory
     * @param callable $expr
     * @return mixed
     */
    public static function chdir(string $directory, callable $expr)
    {
        if (\PHP_OS_FAMILY !== 'Windows') {
            return $expr();
        }

        /** @var string $before */
        $before = \getcwd();

        try {
            self::from($directory);

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
        static $kernel32 = null;

        if (\ZEND_THREAD_SAFE) {
            @\trigger_error(self::PHP_ZTS_WARNING);

            ($kernel32 ?? \FFI::load(self::HEADERS_KERNEL32))
                ->SetDllDirectoryA($directory);

            return;
        }

        \chdir($directory);
    }
}
