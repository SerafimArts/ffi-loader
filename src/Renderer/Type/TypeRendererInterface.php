<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Renderer\Type;

interface TypeRendererInterface
{
    /**
     * @param \ReflectionType $type
     * @return bool
     */
    public function match(\ReflectionType $type): bool;
}
