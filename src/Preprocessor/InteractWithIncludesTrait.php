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
 * @mixin IncludesProviderInterface
 */
trait InteractWithIncludesTrait
{
    /**
     * @var array|string[]
     */
    protected array $includes = [];

    /**
     * @param string $directory
     * @return void
     */
    public function includeFrom(string $directory): void
    {
        if (! \is_dir($directory)) {
            return;
        }

        $this->includes[] = $this->normalizeDirectory($directory);
    }

    /**
     * @param string $directory
     * @return string
     */
    private function normalizeDirectory(string $directory): string
    {
        return \rtrim(\str_replace('\\', '/', \realpath($directory)), '/');
    }

    /**
     * @param string $file
     * @param string|null $localDirectory
     * @return string
     */
    public function lookup(string $file, string $localDirectory = null): string
    {
        if (\is_string($localDirectory) && \is_file($localDirectory . '/' . $file)) {
            return $localDirectory . '/' . $file;
        }

        foreach ($this->includes as $dir) {
            if (\is_file($dir . '/' . $file)) {
                return $dir . '/' . $file;
            }
        }

        return './' . $file;
    }
}
