<?php
declare(strict_types=1);

namespace Paysera\Component\ObjectWrapper\Exception;

class MissingItemException extends InvalidItemException
{
    public function __construct(string $key, \Exception $previous = null)
    {
        parent::__construct($key, sprintf('Missing required key "%s"', $key), $previous);
    }
}
