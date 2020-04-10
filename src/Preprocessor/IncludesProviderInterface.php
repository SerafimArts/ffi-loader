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
 * Interface IncludesProviderInterface
 */
interface IncludesProviderInterface
{
    /**
     * @param string $directory
     * @return void
     */
    public function includeFrom(string $directory): void;

    /**
     * @param string $file
     * @param string|null $localDirectory
     * @return string
     */
    public function lookup(string $file, string $localDirectory = null): string;
}
