<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Attribute;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Immutable;
use Serafim\FFILoader\CallingConvention;
use Serafim\FFILoader\CharSet;

/**
 * @psalm-import-type CharSet from CharSet
 * @psalm-import-type CallingConvention from CallingConvention
 */
#[\Attribute(flags: \Attribute::TARGET_METHOD)]
final class LibImport
{
    /**
     * Gets the name of the library that contains the entry point.
     *
     * @readonly
     * @var string|null
     */
    #[Immutable]
    public ?string $name;

    /**
     * Indicates the name or ordinal of the library entry point to be called.
     *
     * In the case that the function name is not specified, the name of the
     * function where this attribute is defined should be used.
     *
     * @readonly
     * @var string|null
     */
    #[Immutable]
    public ?string $function;

    /**
     * Indicates the calling convention of an entry point.
     *
     * @psalm-var CallingConvention
     * @readonly
     * @var int
     */
    #[Immutable]
    public int $abi;

    /**
     * Indicates whether unmanaged methods that have {@see int} return values
     * are directly translated or whether {@see int} return values are
     * automatically converted to exceptions if the value differs from 0.
     *
     * @readonly
     * @var bool
     */
    #[Immutable]
    public bool $preserveSig;

    /**
     * Indicates how to marshal string parameters to the method and
     * controls name mangling.
     *
     * @var string|null
     */
    public ?string $encoding;

    /**
     * @psalm-param CharSet $charSet
     * @psalm-param CallingConvention $abi
     */
    public function __construct(
        string $name = null,
        string $function = null,
        string $encoding = null,
        #[ExpectedValues(valuesFromClass: CallingConvention::class)]
        int $abi = CallingConvention::CC_AUTO,
        bool $preserveSig = false,
    ) {
        $this->name = $name;
        $this->function = $function;
        $this->encoding = $encoding;
        $this->abi = $abi;
        $this->preserveSig = $preserveSig;
    }
}
