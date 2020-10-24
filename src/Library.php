<?php

/**
 * This file is part of ffi-loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

abstract class Library implements LibraryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDirectory(): string
    {
        return \dirname($this->getBinary()) ?: \getcwd();
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getBinary(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggestion(): string
    {
        return 'Your OS (' . \PHP_OS_FAMILY . ') does not support this library';
    }

    /**
     * {@inheritDoc}
     */
    public function getDirectives(): iterable
    {
        return [];
    }
}
