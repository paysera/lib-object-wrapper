<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Tests;

use PHPUnit\Framework\TestCase;

class ClassLoadingTest extends TestCase
{
    public function testClassesLoadWithoutDeprecations(): void
    {
        $script = strtr(
            <<<'PHP'
set_error_handler(function ($type, $message, $file) {
    if (strpos((string)realpath($file), SOURCE_DIRECTORY . DIRECTORY_SEPARATOR) === 0) {
        echo $message, PHP_EOL;
    }

    return true;
});
require AUTOLOAD_FILE;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SOURCE_DIRECTORY, FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $class = 'Paysera\Component\ObjectWrapper\\'
        . strtr(substr($file->getPathname(), strlen(SOURCE_DIRECTORY) + 1, -4), DIRECTORY_SEPARATOR, '\\');
    if (!class_exists($class) && !interface_exists($class, false) && !trait_exists($class, false)) {
        echo 'Not loaded: ', $class, PHP_EOL;
    }
}
PHP
            ,
            [
                'SOURCE_DIRECTORY' => var_export(realpath(dirname(__DIR__) . '/src'), true),
                'AUTOLOAD_FILE' => var_export(dirname(__DIR__) . '/vendor/autoload.php', true),
            ]
        );

        exec(
            sprintf('%s -n -d error_reporting=-1 -r %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($script)),
            $output,
            $exitCode
        );

        $this->assertSame(['exitCode' => 0, 'output' => []], ['exitCode' => $exitCode, 'output' => $output]);
    }
}
