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
 * @mixin DefinesProviderInterface
 */
trait InteractWithDefinesTrait
{
    /**
     * @var array|string[]
     */
    protected array $defines = [];

    /**
     * @param string $define
     * @param string $value
     * @return void
     */
    public function define(string $define, string $value = ''): void
    {
        $this->defines[$define] = $value;
    }

    /**
     * @param string $define
     * @return void
     */
    public function undef(string $define): void
    {
        unset($this->defines[$define]);
    }

    /**
     * @param string $define
     * @return bool
     */
    public function defined(string $define): bool
    {
        return isset($this->defines[$define]) || \array_key_exists($define, $this->defines);
    }
}
