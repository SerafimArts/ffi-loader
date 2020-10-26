<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

/**
 * Specifies the calling convention required to call methods
 * implemented in unmanaged code.
 *
 * @psalm-type CallingConvention = CallingConvention::CC_*
 */
final class CallingConvention
{
    /**
     * @var int
     */
    public const CC_AUTO = 0;

    /**
     * The caller cleans the stack. This enables calling functions with varargs,
     * which makes it appropriate to use for methods that accept a variable
     * number of parameters, such as Printf.
     *
     * @var int
     */
    public const CC_CDECL = 1;

    /**
     * @var int
     */
    public const CC_FAST_CALL = 2;

    /**
     * The callee cleans the stack.
     *
     * @var int
     */
    public const CC_STD_CALL = 3;

    /**
     * The first parameter is the this pointer and is stored in register
     * ECX. Other parameters are pushed on the stack. This calling convention
     * is used to call methods on classes exported from an unmanaged DLL.
     *
     * @var int
     */
    public const CC_THIS_CALL = 4;

    /**
     * @var int
     */
    public const CC_CLR_CALL = 5;

    /**
     * @var int
     */
    public const CC_VECTOR_CALL = 6;
}
