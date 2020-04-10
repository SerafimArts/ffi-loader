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
 * Class Library
 */
abstract class Library implements LibraryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOutputDirectory(): string
    {
        return __DIR__ . '/../out';
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion(string $library): string
    {
        return '1.0.0';
    }

    /**
     * {@inheritDoc}
     */
    public function suggest(OperatingSystem $os, BitDepth $bits): ?string
    {
        return null;
    }
}
