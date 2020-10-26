<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Renderer\Type;

abstract class NamedTypeRenderer implements TypeRendererInterface
{
    /**
     * @param \ReflectionNamedType $type
     * @return bool
     */
    abstract protected function is(\ReflectionNamedType $type): bool;

    /**
     * @param \ReflectionType $type
     * @return bool
     */
    public function match(\ReflectionType $type): bool
    {
        return $type instanceof \ReflectionNamedType && $this->is($type);
    }
}
