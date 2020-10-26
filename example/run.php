<?php

use Example\User32;
use Serafim\FFILoader\Loader;

require __DIR__ . '/../vendor/autoload.php';

$user32 = Loader::get(User32::class);

$user32->messageBox(0, 'Привет мир!', 'Заголовок сообщеньки', 2);

