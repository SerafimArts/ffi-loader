<?php

/**
 * This file is part of ffi-loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use Phplrt\Contracts\Source\ReadableInterface;

use Serafim\Preprocessor\PreprocessorInterface;

/**
 * @psalm-import-type SourceEntry from PreprocessorInterface
 * @see PreprocessorInterface
 */
interface LibraryInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getBinary(): ?string;

    /**
     * @return string
     */
    public function getDirectory(): string;

    /**
     * @return string
     */
    public function getSuggestion(): string;

    /**
     * @psalm-return SourceEntry
     * @return string|resource|ReadableInterface|\SplFileInfo
     */
    public function getHeaders();

    /**
     * @psalm-return iterable<string, mixed>
     * @return iterable
     */
    public function getDirectives(): iterable;
}
