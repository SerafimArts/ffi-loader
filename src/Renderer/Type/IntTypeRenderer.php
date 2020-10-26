<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Renderer\Type;

final class IntTypeRenderer extends NamedTypeRenderer implements ArgumentRendererInterface, ReturnTypeRendererInterface
{
    /**
     * @param string $suffix
     * @return string
     */
    private function render(string $suffix): string
    {
        $result = \PHP_INT_SIZE === 8 ? 'int64_t' : 'int32_t';

        return $result . $suffix;
    }

    /**
     * @param \ReflectionType $type
     * @param \ReflectionParameter $param
     * @return string
     */
    public function renderArgument(\ReflectionType $type, \ReflectionParameter $param): string
    {
        $suffix = $type->allowsNull() || $param->isPassedByReference() ? '*' : '';

        return $this->render($suffix);
    }

    /**
     * @param \ReflectionType $type
     * @param \ReflectionFunctionAbstract $fn
     * @return string
     */
    public function renderReturnType(\ReflectionType $type, \ReflectionFunctionAbstract $fn): string
    {
        return $this->render($type->allowsNull() ? '*' : '');
    }

    /**
     * @param \ReflectionNamedType $type
     * @return bool
     */
    protected function is(\ReflectionNamedType $type): bool
    {
        return $type->getName() === 'int';
    }
}
