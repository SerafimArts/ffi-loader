<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Serafim\FFILoader\Preprocessor\DefaultDefinesTrait;
use Serafim\FFILoader\Preprocessor\Expression;
use Serafim\FFILoader\Preprocessor\InteractWithDefinesTrait;
use Serafim\FFILoader\Preprocessor\InteractWithIncludesTrait;
use Serafim\FFILoader\Preprocessor\Lexer;

/**
 * Class Preprocessor
 */
class Preprocessor implements PreprocessorInterface
{
    use InteractWithIncludesTrait;
    use InteractWithDefinesTrait;
    use DefaultDefinesTrait;

    /**
     * @var string
     */
    private const ERROR_FILE_READING = 'Error while reading file "%s": %s';

    /**
     * @var bool
     */
    public bool $keepComments = true;

    /**
     * Enable or disable tolerant (ignoring errors) parsing
     *
     * @var bool
     */
    public bool $tolerant = true;

    /**
     * @var bool
     */
    public bool $minify = false;

    /**
     * @var LexerInterface
     */
    private LexerInterface $lexer;

    /**
     * @var Expression
     */
    private Expression $expr;

    /**
     * PreProcessor constructor.
     */
    public function __construct()
    {
        $this->lexer = new Lexer();
        $this->expr = new Expression($this);

        $this->bootDefaultDefinesTrait();
    }

    /**
     * @param string $pathname
     * @return string
     * @throws \RuntimeException
     */
    public function file(string $pathname): string
    {
        if (! \is_file($pathname)) {
            throw new \InvalidArgumentException(\sprintf('File "%s" not found', $pathname));
        }

        if (! \is_readable($pathname)) {
            throw new \InvalidArgumentException(\sprintf('File "%s" not readable', $pathname));
        }

        $source = @\file_get_contents($pathname = \trim($pathname));

        return $this->source($source, \dirname($pathname));
    }

    /**
     * @param string $source
     * @param string|null $cwd
     * @return string
     * @throws \RuntimeException
     * @throws \Throwable
     */
    public function source(string $source, string $cwd = null): string
    {
        $result = '';

        foreach ($this->execute($source, $cwd) as $value) {
            $result .= $value;
        }

        if ($this->minify) {
            $result = \preg_replace('/\n+/ium', "\n", $result);
        }

        return $result;
    }

    /**
     * @param string $source
     * @param string|null $dir
     * @return \Traversable|string[]
     * @throws \Throwable
     */
    private function execute(string $source, string $dir = null): \Traversable
    {
        $assertions = [];

        foreach ($this->lexer->lex($source) as $token) {
            $next = $this->next($assertions, $token, $dir);

            switch (true) {
                case \is_string($next):
                    yield $next;
                    break;

                case \is_bool($next):
                    $assertions[$this->key($token)] = $next;
                    break;

                case \count($assertions) > 0:
                    \array_pop($assertions);
                    break;

                case $next === null && $this->tolerant === false:
                    throw new \LogicException('An extra closing (#endif) directive');
            }
        }

        if ($this->tolerant === false && \count($assertions) > 0) {
            throw new \LogicException('Missing closing (#endif) directive');
        }
    }

    /**
     * @param array $assertions
     * @param TokenInterface $token
     * @param string|null $dir
     * @return bool|string|null
     * @throws \Throwable
     */
    private function next(array $assertions, TokenInterface $token, string $dir = null)
    {
        switch ($token->getName()) {
            case 'T_LOCAL_INCLUDE':
            case 'T_GLOBAL_INCLUDE':
                $pathname = $this->lookup($token->getValue(), $dir);

                if (! \is_file($pathname) || ! \is_readable($pathname)) {
                    throw new \LogicException(\sprintf('Can not include "%s"', $token->getValue()));
                }

                return $this->file($pathname);

            case 'T_IFNDEF':
                return ! $this->defined($token->getValue());

            case 'T_IFDEF':
                return $this->defined($token->getValue());

            case 'T_IF':
                return $this->expr->eval($this->preprocess($token->getValue()));

            case 'T_ENDIF':
                return null;

            case 'T_DEFINE':
                $this->doDefine($token);

                return '#define ' . $token->getValue();

            case 'T_UNDEF':
                $this->doUndef($token);

                return '#undef ' . $token->getValue();

            case 'T_COMMENT':
            case 'T_GROUP_COMMENT':
                if ($this->keepComments && $this->matchAllAssertions($assertions)) {
                    return $this->preprocess($token->getValue());
                }

                return '';
        }

        if ($this->matchAllAssertions($assertions)) {
            return $this->preprocess($token->getValue());
        }

        return '';
    }

    /**
     * @param TokenInterface $token
     * @return void
     */
    private function doDefine(TokenInterface $token): void
    {
        $chunks = \explode(' ', $token->getValue());

        $this->define(
            \array_shift($chunks),
            \str_replace("\\\n", "\n", \implode(' ', $chunks))
        );
    }

    /**
     * @param TokenInterface $token
     * @return void
     */
    private function doUndef(TokenInterface $token): void
    {
        $this->undef(\trim($token->getValue()));
    }

    /**
     * @param array $assertions
     * @return bool
     */
    private function matchAllAssertions(array $assertions): bool
    {
        foreach ($assertions as $assertion) {
            if (! $assertion) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $source
     * @return string
     */
    private function preprocess(string $source): string
    {
        foreach ($this->defines as $from => $to) {
            $pattern = \sprintf('/\b%s\b/sum', \preg_quote($from, '/'));

            $source = \preg_replace($pattern, $to, $source);
        }

        return $source;
    }

    /**
     * @param TokenInterface $token
     * @return string
     */
    private function key(TokenInterface $token): string
    {
        return \hash('crc32', $token->getValue() . ':' . $token->getOffset());
    }
}
