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
use Psr\Log\NullLogger;
use Serafim\FFILoader\Attribute\LibImport;
use Serafim\FFILoader\Attribute\Reader;
use Serafim\FFILoader\Context\Declaration;
use Serafim\FFILoader\Renderer\RendererInterface;
use Serafim\Flux\Type;

/**
 * @template T
 *
 * @internal Context is an internal library class, please do not use it in your code.
 * @psalm-internal Serafim\FFILoader
 *
 */
final class Context
{
    /**
     * List of available methods
     *
     * @psalm-var array<string, Declaration>
     * @var array|Declaration[]
     */
    private array $methods = [];

    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @param \ReflectionClass $class
     * @param RendererInterface $renderer
     * @param LoggerInterface|null $logger
     */
    public function __construct(\ReflectionClass $class, RendererInterface $renderer, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();

        foreach ($class->getMethods() as $method) {
            /** @var LibImport|null $attribute */
            if ($attribute = Reader::getAttributeInstance($method, LibImport::class)) {
                $this->loadAttribute($renderer, $method, $attribute);
            }
        }
    }


    /**
     * @param RendererInterface $renderer
     * @param \ReflectionFunctionAbstract $fn
     * @param LibImport $lib
     */
    private function loadAttribute(RendererInterface $renderer, \ReflectionFunctionAbstract $fn, LibImport $lib): void
    {
        $declaration = $renderer->renderFunction($fn, $lib);

        $this->logger->debug('Register declaration [' . $fn->getName() . ']', [
            'library'     => $lib->name,
            'function'    => $fn->getName(),
            'declaration' => $declaration,
        ]);

        $this->methods[$fn->getName()] = new Declaration($declaration, $fn, $lib);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        $declaration = $this->methods[$method] ?? null;

        if ($declaration === null) {
            throw new \BadMethodCallException('Function ' . $method . ' not exists');
        }


        return $declaration(...$arguments);
    }
}
