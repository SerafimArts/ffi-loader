<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Renderer;

use Serafim\FFILoader\CallingConvention;

/**
 * @psalm-import-type CallingConvention from CallingConvention
 */
final class CallingConventionRenderer
{
    /**
     * @psalm-param CallingConvention $abi
     *
     * @param int $abi
     * @return string
     */
    public static function render(int $abi): string
    {
        switch ($abi) {
            case CallingConvention::CC_CDECL:
                return '__cdecl';

            case CallingConvention::CC_FAST_CALL:
                return '__fastcall';

            case CallingConvention::CC_STD_CALL:
                return '__stdcall';

            case CallingConvention::CC_THIS_CALL:
                return '__thiscall';

            case CallingConvention::CC_CLR_CALL:
                return '__clrcall';

            case CallingConvention::CC_VECTOR_CALL:
                return '__vectorcall';

            default:
                return '';
        }
    }
}
