<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Serafim\FFILoader\BitDepth;
use Serafim\FFILoader\Library;
use Serafim\FFILoader\OperatingSystem;

final class Kernel32 extends Library
{
    /** {@inheritDoc} */
    public function getName(): string
    {
        return 'kernel32';
    }

    /** {@inheritDoc} */
    public function getHeaders(): string
    {
        return __DIR__ . '/kernel32.h';
    }

    /** {@inheritDoc} */
    public function getVersion(string $library): string
    {
        $version = \FFI::cdef('extern unsigned long GetVersion();', $library)->GetVersion();

        return \vsprintf('%s.%s.%s', [
            $version & 0xff,
            $version >> 8,
            $version < 0x80000000 ? ($version >> 8 & 0xff) : 0,
        ]);
    }

    /** {@inheritDoc} */
    public function getLibrary(OperatingSystem $os, BitDepth $bits): ?string
    {
        return $os->isWindows() ? 'kernel32.dll' : null;
    }
}
