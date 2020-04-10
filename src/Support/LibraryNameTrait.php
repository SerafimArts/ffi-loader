<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Support;

use Serafim\FFILoader\LibraryInterface;

/**
 * Trait LibraryNameTrait
 */
trait LibraryNameTrait
{
    /**
     * @param LibraryInterface $library
     * @param string $delimiter
     * @return string
     */
    protected function lowerCase(LibraryInterface $library, string $delimiter = '_'): string
    {
        $escaped = \preg_quote($delimiter, '/');

        $name = \strtolower($library->getName());
        $name = \preg_replace('/\W+/u', $delimiter, $name);
        $name = \preg_replace('/' . $escaped . '+/', $delimiter, $name);

        return \trim($name, $delimiter);
    }

    /**
     * @param LibraryInterface $library
     * @param string $name
     * @return string
     */
    protected function defineName(LibraryInterface $library, string $name): string
    {
        return '__' . $this->lowerCase($library) . '_' . $name . '__';
    }
}
