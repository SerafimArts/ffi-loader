<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Context;

use JetBrains\PhpStorm\Immutable;
use Serafim\FFILoader\Attribute\LibImport;
use Serafim\FFILoader\Renderer\RendererInterface;
use Serafim\Flux\Type;

final class Declaration
{
    /**
     * @var string
     */
    private const ERROR_PRESERVE_SIG = 'Function %s (mapped to %s) returned error code %d';

    /**
     * @readonly
     * @var string
     */
    #[Immutable]
    public string $function;

    /**
     * @readonly
     * @var string
     */
    #[Immutable]
    public string $method;

    /**
     * @readonly
     * @var string
     */
    #[Immutable]
    public string $declaration;

    /**
     * @readonly
     * @var \FFI
     */
    #[Immutable]
    public \FFI $ffi;

    /**
     * @readonly
     * @var LibImport
     */
    #[Immutable]
    public LibImport $lib;

    /**
     * @param string $declaration
     * @param \ReflectionFunctionAbstract $fn
     * @param LibImport $lib
     */
    public function __construct(string $declaration, \ReflectionFunctionAbstract $fn, LibImport $lib)
    {
        $this->lib = $lib;
        $this->function = $lib->function ?? $fn->getName();
        $this->declaration = $declaration;
        $this->ffi = \FFI::cdef($this->declaration, $lib->name);
        $this->method = $fn->getName();
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     */
    public function __invoke(&...$arguments): mixed
    {
        $passed = [];

        foreach ($arguments as $index => $value) {
            $passed[$index] = $this->encode($value);
        }

        $function = $this->function;

        $result = $this->ffi->$function(...$passed);

        if ($this->lib->preserveSig && \is_int($result) && $result !== 0) {
            $error = \sprintf(self::ERROR_PRESERVE_SIG, $this->method, $this->function, $result);

            throw new \RuntimeException($error, $result);
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function encode(mixed $value): mixed
    {
        switch (true) {
            case \is_string($value) && $this->lib->encoding !== null:
                return Type::string(\iconv('utf-8', $this->lib->encoding, $value));

            default:
                return $value;
        }
    }
}
