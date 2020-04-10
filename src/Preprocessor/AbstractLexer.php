<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;

/**
 * Class AbstractLexer
 */
abstract class AbstractLexer implements LexerInterface
{
    /**
     * @var string
     */
    protected const PCRE_FLAGS = \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;

    /**
     * @var string
     */
    protected string $pcre;

    /**
     * @param string $source
     * @param int $offset
     * @return iterable|TokenInterface[]
     */
    public function lex($source, int $offset = 0): iterable
    {
        \assert(\is_string($source), 'Source argument MUST be a string');

        \preg_match_all($this->pcre, $source, $matches, static::PCRE_FLAGS, $offset);

        foreach ($matches as $match) {
            $name = \array_pop($match);
            $offset = $match[0][1];
            $value = $match[\array_key_last($match)][0];

            yield new Token($name, $value, $offset);
        }
    }

    /**
     * @param iterable $tokens
     * @return string
     */
    protected function compile(iterable $tokens): string
    {
        $groups = [];

        foreach ($tokens as $name => $pcre) {
            $groups[] = "(?:(?:$pcre)(*MARK:$name))";
        }

        return \vsprintf('/\\G(?|%s)/Ssum', [
            \implode('|', $groups),
        ]);
    }
}
