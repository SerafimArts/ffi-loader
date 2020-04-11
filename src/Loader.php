<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader;

use Serafim\FFILoader\Exception\LibraryException;
use Serafim\FFILoader\Support\LibraryNameTrait;

/**
 * Class Loader
 */
class Loader
{
    use LibraryNameTrait;

    /**
     * @var string
     */
    private const ERROR_LOCAL_LIBRARY =
        'Unable to retrieve library information. %s';

    /**
     * @var string
     */
    private const ERROR_SUGGEST = 'Your operating system (%s %s) may not be supported.';

    /**
     * @var OperatingSystem
     */
    private OperatingSystem $os;

    /**
     * @var BitDepth
     */
    private BitDepth $bits;

    /**
     * @var PreprocessorInterface
     */
    private PreprocessorInterface $pre;

    /**
     * Loader constructor.
     *
     * @param OperatingSystem|null $os
     * @param BitDepth|null $bits
     */
    public function __construct(OperatingSystem $os = null, BitDepth $bits = null)
    {
        $this->os = $os ?? OperatingSystem::current();
        $this->bits = $bits ?? BitDepth::current();

        $this->pre = new Preprocessor();
        $this->pre->keepComments = false;
    }

    /**
     * @return PreprocessorInterface
     */
    public function preprocessor(): PreprocessorInterface
    {
        return $this->pre;
    }

    /**
     * @param LibraryInterface $library
     * @return LibraryInformation
     */
    public function load(LibraryInterface $library): LibraryInformation
    {
        $binary = $this->getBinaries($library);
        $version = $library->getVersion($binary);

        $file = $this->getOutputHeaderFile($library, $binary, $version);

        $this->pre->define($this->defineName($library, 'bin'), $binary);
        $this->pre->define($this->defineName($library, 'version'), $version);
        $this->pre->define($this->defineName($library, 'name'), $library->getName());

        // Compile
        if (! \is_file($file)) {
            $headers = $this->pre->file($library->getHeaders());

            \file_put_contents($file, $headers, \LOCK_EX);
        } else {
            $headers = \file_get_contents($file);
        }

        try {
            $ffi = \FFI::cdef($headers, $binary);

            return new LibraryInformation($binary, $version, $library->getName(), $ffi);
        } catch (\Throwable $e) {
            @\unlink($file);

            throw new LibraryException($e->getMessage());
        }
    }

    /**
     * @param LibraryInterface $library
     * @return string
     */
    private function getBinaries(LibraryInterface $library): string
    {
        $from = $library->getLibrary($this->os, $this->bits);

        if ($from === null) {
            throw new LibraryException(\sprintf(self::ERROR_LOCAL_LIBRARY, $this->suggest($library)));
        }

        return \realpath($from) ?: $from;
    }

    /**
     * @param LibraryInterface $library
     * @return string
     */
    private function suggest(LibraryInterface $library): string
    {
        $suggest = $library->suggest($this->os, $this->bits);

        return $suggest ?? \sprintf(self::ERROR_SUGGEST, (string)$this->os, (string)$this->bits);
    }

    /**
     * @param LibraryInterface $lib
     * @param string $bin
     * @param string $version
     * @return string
     */
    protected function getOutputHeaderFile(LibraryInterface $lib, string $bin, string $version): string
    {
        $filename = \vsprintf('%s-%s-%s-%s.h', [
            $this->lowerCase($lib, '-'),
            $version,
            strtolower((string)$this->os),
            (string)$this->bits,
        ]);

        return $lib->getOutputDirectory() . '/' . $filename;
    }
}
