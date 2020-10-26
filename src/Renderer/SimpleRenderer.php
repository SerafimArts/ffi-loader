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
use Serafim\FFILoader\Attribute\Reader;
use Serafim\FFILoader\Attribute\Type;
use Serafim\FFILoader\Context;
use Serafim\FFILoader\Renderer\Type\ArgumentRendererInterface;
use Serafim\FFILoader\Renderer\Type\FloatTypeRenderer;
use Serafim\FFILoader\Renderer\Type\IntTypeRenderer;
use Serafim\FFILoader\Renderer\Type\MixedTypeRenderer;
use Serafim\FFILoader\Renderer\Type\ReturnTypeRendererInterface;
use Serafim\FFILoader\Renderer\Type\StringTypeRenderer;
use Serafim\FFILoader\Renderer\Type\TypeRendererInterface;

/**
 * @psalm-import-type ReflectionAttributeProvider from Context
 * @see Context
 */
class SimpleRenderer implements RendererInterface
{
    /**
     * @var string
     */
    private const ERROR_BAD_RETURN_TYPE = 'Unsupported PHP return type hint <%s> of %s() method';

    /**
     * @var string
     */
    private const ERROR_BAD_ARGUMENT_TYPE = 'Unsupported PHP argument type hint <%s> of $%s parameter';

    /**
     * Expected Format:
     *  "extern <type> <calling_convention> <name>(<arguments>);"
     *
     * @var string
     */
    private const METHOD_TEMPLATE = 'extern %s %s %s(%s);';

    /**
     * @var string
     */
    private const NO_ARGUMENTS = 'void';

    /**
     * @var string[]
     */
    private const DEFAULT_TYPES = [
        IntTypeRenderer::class,
        MixedTypeRenderer::class,
        StringTypeRenderer::class,
        FloatTypeRenderer::class,
    ];

    /**
     * @var array|ReturnTypeRendererInterface[]
     */
    private array $return = [];

    /**
     * @var array|ArgumentRendererInterface[]
     */
    private array $arguments = [];

    /**
     * @var \ReflectionNamedType
     */
    private \ReflectionNamedType $mixed;

    /**
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $this->mixed = $this->getReflectionMixedType();

        $this->bootDefaultTypes();
    }

    /**
     * @return void
     */
    private function bootDefaultTypes(): void
    {
        foreach (self::DEFAULT_TYPES as $type) {
            $this->type(new $type());
        }
    }

    /**
     * @param TypeRendererInterface $renderer
     */
    public function type(TypeRendererInterface $renderer): void
    {
        if ($renderer instanceof ReturnTypeRendererInterface) {
            $this->return[] = $renderer;
        }

        if ($renderer instanceof ArgumentRendererInterface) {
            $this->arguments[] = $renderer;
        }
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    private function getReflectionMixedType(): mixed
    {
        return (new \ReflectionMethod($this, 'getReflectionMixedType'))
            ->getReturnType()
        ;
    }

    /**
     * @param \ReflectionFunctionAbstract $fn
     * @param LibImport $lib
     * @return string
     */
    public function renderFunction(\ReflectionFunctionAbstract $fn, LibImport $lib): string
    {
        return \vsprintf(self::METHOD_TEMPLATE, [
            $this->renderReturnType($fn),
            CallingConventionRenderer::render($lib->abi),
            $lib->function ?? $fn->getName(),
            $this->renderArguments($fn),
        ]);
    }

    /**
     * @param \ReflectionParameter $param
     * @return string
     */
    private function renderArgumentType(\ReflectionParameter $param): string
    {
        if ($result = $this->renderTypeFromAttribute($param)) {
            return $result;
        }

        $type = $param->getType() ?? $this->mixed;

        foreach ($this->arguments as $renderer) {
            if ($renderer->match($type)) {
                return $renderer->renderArgument($type, $param);
            }
        }

        throw new \LogicException(\sprintf(self::ERROR_BAD_ARGUMENT_TYPE, $this->phpTypeToString($type), $param->getName()));
    }

    /**
     * @param \ReflectionFunctionAbstract $fn
     * @return string
     */
    private function renderReturnType(\ReflectionFunctionAbstract $fn): string
    {
        if ($result = $this->renderTypeFromAttribute($fn)) {
            return $result;
        }

        $type = $fn->getReturnType() ?? $this->mixed;

        foreach ($this->return as $renderer) {
            if ($renderer->match($type)) {
                return $renderer->renderReturnType($type, $fn);
            }
        }

        throw new \LogicException(\sprintf(self::ERROR_BAD_RETURN_TYPE, $this->phpTypeToString($type), $fn->getName()));
    }

    /**
     * @param \ReflectionType $type
     * @return string
     */
    private function phpTypeToString(\ReflectionType $type): string
    {
        if ($type instanceof \ReflectionNamedType) {
            return ($type->allowsNull() ? '?' : '') . $type->getName();
        }

        if ($type instanceof \ReflectionUnionType) {
            $result = [];

            foreach ($type->getTypes() as $current) {
                $result[] = $current->getName();
            }

            if ($type->allowsNull() && ! \in_array('null', $result, true)) {
                $result[] = 'null';
            }

            return \implode('|', $result);
        }

        return 'mixed';
    }

    /**
     * @psalm-param ReflectionAttributeProvider $ctx
     *
     * @param \Reflector $ctx
     * @return string|null
     */
    private function renderTypeFromAttribute(\Reflector $ctx): ?string
    {
        /** @var Type|null $attribute */
        if ($attribute = Reader::getAttributeInstance($ctx, Type::class)) {
            return (string)$attribute;
        }

        return null;
    }

    /**
     * @param \ReflectionFunctionAbstract $fn
     * @return string
     */
    private function renderArguments(\ReflectionFunctionAbstract $fn): string
    {
        if ($fn->getNumberOfParameters() === 0) {
            return self::NO_ARGUMENTS;
        }

        $result = [];

        foreach ($fn->getParameters() as $parameter) {
            $result[] = $this->renderArgument($parameter);
        }

        return \implode(', ', $result);
    }

    /**
     * @param \ReflectionParameter $param
     * @return string
     */
    private function renderArgument(\ReflectionParameter $param): string
    {
        $suffix = '';

        if ($param->isVariadic()) {
            $suffix = '...';
        }

        return $this->renderArgumentType($param) . ' ' . $param->getName() . $suffix;
    }
}
