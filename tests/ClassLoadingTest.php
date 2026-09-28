<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Tests;

use Paysera\Component\ObjectWrapper\Exception\InvalidItemException;
use Paysera\Component\ObjectWrapper\Exception\InvalidItemTypeException;
use Paysera\Component\ObjectWrapper\Exception\MissingItemException;
use Paysera\Component\ObjectWrapper\ObjectWrapper;
use PHPUnit\Framework\TestCase;

class ClassLoadingTest extends TestCase
{
    public function testClassesLoadWithoutDeprecations()
    {
        $script = sprintf(
            'set_error_handler(function ($type, $message, $file) {'
            . ' if (strpos((string)realpath($file), %s) === 0) { echo $message, PHP_EOL; }'
            . ' return true;'
            . ' });'
            . ' require %s;'
            . ' foreach (%s as $class) { class_exists($class); }',
            var_export(realpath(dirname(__DIR__) . '/src') . DIRECTORY_SEPARATOR, true),
            var_export(dirname(__DIR__) . '/vendor/autoload.php', true),
            var_export(
                [
                    ObjectWrapper::class,
                    InvalidItemException::class,
                    InvalidItemTypeException::class,
                    MissingItemException::class,
                ],
                true
            )
        );

        exec(
            sprintf('%s -d error_reporting=-1 -r %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg($script)),
            $output,
            $exitCode
        );

        $this->assertSame(['exitCode' => 0, 'output' => []], ['exitCode' => $exitCode, 'output' => $output]);
    }
}
