<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

/**
 * Trait DefaultDefinesTrait
 */
trait DefaultDefinesTrait
{
    /**
     * @param string $define
     * @param string $value
     * @return void
     */
    abstract public function define(string $define, string $value): void;

    /**
     * @param string $define
     * @param string $value
     * @return void
     */
    private function defineWrapped(string $define, string $value): void
    {
        $this->define('_' . $define, $value);
        $this->define('__' . $define, $value);
        $this->define('__' . $define . '__', $value);
    }

    /**
     * @return void
     */
    private function bootDefaultDefinesTrait(): void
    {
        $define = function (string ...$defines): void {
            foreach ($defines as $def) {
                $this->defineWrapped($def, '1');
            }
        };

        $define(\PHP_INT_SIZE === 8 ? 'amd64' : 'i386');

        switch (\PHP_OS_FAMILY) {
            case 'Windows':
                $define('cygwin', 'mingw32', 'windows', 'win');
                $define(\PHP_INT_SIZE === 8 ? 'win64' : 'win32');
                break;

            case 'Linux':
                $define('linux');
                break;

            case 'Darwin':
                $define('osx', 'apple', 'macosx');
                break;

            case 'BSD':
                $define('freebsd', 'freebsd_kernel', 'dragonfly', 'bsdi', 'openbsd');
                break;

            case 'Solaris':
                $define('sun', 'svr4', 'solaris');
                break;
        }
    }
}
