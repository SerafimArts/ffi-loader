<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Renderer;

use Serafim\FFILoader\Attribute\LibImport;

interface RendererInterface
{
    /**
     * @param \ReflectionFunctionAbstract $fn
     * @param LibImport $lib
     * @return string
     */
    public function renderFunction(\ReflectionFunctionAbstract $fn, LibImport $lib): string;
}
