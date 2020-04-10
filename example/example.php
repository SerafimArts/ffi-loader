<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Serafim\FFILoader\Loader;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Kernel32.php';

// See: https://docs.microsoft.com/en-us/windows/win32/api/sysinfoapi/nf-sysinfoapi-getcomputernameexa

$loader = new Loader();
$ffi = $loader->load(new Kernel32())->ffi;

$descriptions = [
    'NetBIOS',
    'DNS hostname',
    'DNS domain',
    'DNS fully-qualified',
    'Physical NetBIOS',
    'Physical DNS hostname',
    'Physical DNS domain',
    'Physical DNS fully-qualified',
];

function buffer_to_string(\FFI\CData $data): string
{
    $result = [];

    foreach ($data as $i) {
        $result[] = $i;
    }

    return \implode($result);
}

foreach ($descriptions as $i => $message) {
    $buffer = \FFI::new('char[256]');
    $size = \FFI::new('uint32_t');
    $size->cdata = 256;

    $ffi->GetComputerNameExA($i, $buffer, FFI::addr($size));

    echo $descriptions[$i] . ': ' . buffer_to_string($buffer) . "\n";
}




