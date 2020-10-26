<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use Psr\Log\LoggerInterface;
use Serafim\FFILoader\Renderer\RendererInterface as Renderer;
use Serafim\FFILoader\Renderer\SimpleRenderer;

final class Loader
{
    /**
     * @psalm-var array<string, Context<class-string>>
     *
     * @var array|Context[]
     */
    private static array $bindings = [];

    /**
     * @var Renderer|null
     */
    private static ?Renderer $defaultRenderer = null;

    /**
     * @template T
     * @psalm-param class-string<T> $class
     * @param string $class
     * @param LoggerInterface|null $logger
     * @return Context<T>
     *
     * @return Context
     * @throws \ReflectionException
     */
    public static function get(string $class, LoggerInterface $logger = null): Context
    {
        return self::$bindings[$class] ??= self::load($class, $logger);
    }

    /**
     * @template T
     * @psalm-param class-string<T> $class
     * @psalm-return Context<T>
     *
     * @param string $class
     * @param LoggerInterface|null $logger
     * @param Renderer|null $renderer
     * @return Context
     * @throws \ReflectionException
     */
    public static function load(string $class, Renderer $renderer = null, LoggerInterface $logger = null): Context
    {
        $renderer ??= self::getDefaultRenderer();

        return new Context(new \ReflectionClass($class), $renderer, $logger);
    }

    /**
     * @return Renderer
     */
    private static function getDefaultRenderer(): Renderer
    {
        return self::$defaultRenderer ??= new SimpleRenderer();
    }

    /**
     * @psalm-param class-string $class
     *
     * @param string $class
     * @return bool
     */
    public static function unload(string $class): bool
    {
        try {
            return isset(self::$bindings[$class]);
        } finally {
            unset(self::$bindings[$class]);
        }
    }
}
