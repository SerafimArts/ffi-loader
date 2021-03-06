<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Type;

interface ReturnTypeRendererInterface extends TypeRendererInterface
{
    /**
     * @param \ReflectionType $type
     * @param \ReflectionFunctionAbstract $fn
     * @return string
     */
    public function renderReturnType(\ReflectionType $type, \ReflectionFunctionAbstract $fn): string;
}
