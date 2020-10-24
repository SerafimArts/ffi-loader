<?php

/**
 * This file is part of ffi-loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use FFI\Exception;
use FFI\ParserException;
use Phplrt\Exception\LineReader;
use Phplrt\Source\File;
use Serafim\FFILoader\Exception\BinaryException;
use Serafim\FFILoader\Exception\EnvironmentException;
use Serafim\FFILoader\Exception\HeadersException;
use Serafim\FFILoader\Exception\LoaderException;
use Serafim\Flux\Library as FFILibrary;
use Serafim\Preprocessor\Exception\PreprocessorException;
use Serafim\Preprocessor\Preprocessor;
use Serafim\Preprocessor\PreprocessorInterface;

/**
 * @psalm-type LibraryRelation = LibraryInterface|class-string<LibraryInterface>
 */
final class Loader
{
    /**
     * @var string
     */
    private const ERROR_LIB_ARGUMENT = 'Library argument must be a class that implements %s or an instance of %$1s';

    /**
     * @var string
     */
    private const ERROR_ENVIRONMENT = 'FFI is not available in this PHP environment';

    /**
     * @var PreprocessorInterface
     */
    private PreprocessorInterface $pre;

    /**
     * @param PreprocessorInterface|null $pre
     */
    public function __construct(PreprocessorInterface $pre = null)
    {
        $this->pre = $pre ?? new Preprocessor();
    }

    /**
     * @psalm-param LibraryRelation $library
     * @param LibraryInterface|string $library
     * @param iterable|array $directives
     * @return \FFI
     */
    public static function load($library, iterable $directives = []): \FFI
    {
        return (new self())->cdef($library, $directives);
    }

    /**
     * @psalm-param LibraryRelation $library
     * @param string|LibraryInterface $library
     * @param iterable|array $directives
     * @return \FFI
     */
    public function cdef($library, iterable $directives = []): \FFI
    {
        if (! FFILibrary::isAvailable()) {
            throw new EnvironmentException(self::ERROR_ENVIRONMENT);
        }

        $library = $this->instance($library);

        try {
            return $this->new($library, $directives);
        } catch (ParserException | PreprocessorException $e) {
            throw new HeadersException($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $message = \vsprintf('%s: %s', [$e->getMessage(), $library->getSuggestion()]);
            throw new BinaryException($message, $e->getCode());
        } catch (\Throwable $e) {
            throw new LoaderException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @psalm-param LibraryRelation $library
     * @param string|LibraryInterface $library
     * @return LibraryInterface
     */
    private function instance($library): LibraryInterface
    {
        if ($library instanceof LibraryInterface) {
            return $library;
        }

        if (\is_subclass_of($library, LibraryInterface::class)) {
            return new $library();
        }

        throw new \InvalidArgumentException(\sprintf(self::ERROR_LIB_ARGUMENT, LibraryInterface::class));
    }

    /**
     * @param LibraryInterface $library
     * @param iterable|array $directives
     * @return \FFI
     */
    private function new(LibraryInterface $library, iterable $directives = []): \FFI
    {
        $headers = $this->compile($library, $directives);
        $current = \getcwd();

        try {
            FFILibrary::setDirectory($library->getDirectory());

            try {
                return \FFI::cdef($headers, $library->getBinary());
            } catch (ParserException $e) {
                $message = $this->parseHeadersError($headers, $e->getMessage());

                throw new ParserException($message, $e->getCode(), $e);
            }
        } finally {
            FFILibrary::setDirectory($current);
        }
    }

    /**
     * @param LibraryInterface $library
     * @param iterable|array $directives
     * @return string
     */
    private function compile(LibraryInterface $library, iterable $directives = []): string
    {
        $preprocessor = clone $this->pre;

        $preprocessor->undef('FFI_SCOPE');
        $preprocessor->undef('FFI_LIB');

        foreach ($library->getDirectives() as $name => $value) {
            $preprocessor->define($name, $value);
        }

        foreach ($directives as $name => $value) {
            $preprocessor->define($name, $value);
        }

        return \implode("\n", [
            \sprintf('#define FFI_SCOPE "%s"', \addcslashes($library->getName(), '"')),
            \sprintf('#define FFI_LIB "%s"', \addcslashes($library->getBinary() ?: '', '"')),
            $preprocessor->process($library->getHeaders())
        ]);
    }

    /**
     * @param string $headers
     * @param string $message
     * @return string
     */
    private function parseHeadersError(string $headers, string $message): string
    {
        $replace = static function (array $matches) use ($headers): string {
            $line = (int)($matches[1] ?? 0);
            $reader = new LineReader(File::fromSources($headers));

            return \sprintf('at line %d in%s > %s%2$s', $line, \PHP_EOL, $reader->readLine($line));
        };

        $result = \preg_replace_callback('/at\hline\h(\d+)/', $replace, $message);

        return \ucfirst((string)($result ?: $message));
    }
}
