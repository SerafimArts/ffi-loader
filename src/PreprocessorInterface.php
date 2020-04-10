<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use Serafim\FFILoader\Preprocessor\DefinesProviderInterface;
use Serafim\FFILoader\Preprocessor\IncludesProviderInterface;

/**
 * Interface PreprocessorInterface
 */
interface PreprocessorInterface extends
    DefinesProviderInterface,
    IncludesProviderInterface
{
    /**
     * @param string $file
     * @return string
     */
    public function file(string $file): string;

    /**
     * @param string $source
     * @param string|null $localDirectory
     * @return string
     */
    public function source(string $source, string $localDirectory = null): string;
}
