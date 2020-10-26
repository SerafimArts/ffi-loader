<?php

namespace Example;

use Serafim\FFILoader\Attribute\LibImport;

interface User32
{
    /**
     * <code>
     *  int MessageBox(HWND hWnd, LPCTSTR lpText, LPCTSTR lpCaption, UINT uType);
     * </code>
     */
    #[LibImport(name: "User32.dll", function: 'MessageBoxW', encoding: 'UTF-16LE//IGNORE')]
    public function messageBox(int $hWnd, string $lpText, string $lpCaption, int $uType): int;
}
