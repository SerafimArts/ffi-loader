<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Type;

final class MixedTypeRenderer extends NamedTypeRenderer implements ArgumentRendererInterface, ReturnTypeRendererInterface
{
    /**
     * @param \ReflectionType $type
     * @param \ReflectionParameter $param
     * @return string
     */
    public function renderArgument(\ReflectionType $type, \ReflectionParameter $param): string
    {
        return 'void*';
    }

    /**
     * @param \ReflectionType $type
     * @param \ReflectionFunctionAbstract $fn
     * @return string
     */
    public function renderReturnType(\ReflectionType $type, \ReflectionFunctionAbstract $fn): string
    {
        return 'void*';
    }

    /**
     * @param \ReflectionNamedType $type
     * @return bool
     */
    protected function is(\ReflectionNamedType $type): bool
    {
        return $type->getName() === 'mixed';
    }
}
