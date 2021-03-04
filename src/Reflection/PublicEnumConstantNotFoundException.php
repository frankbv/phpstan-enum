<?php
namespace Frank\PhpStan\Reflection;

use PHPStan\AnalysedCodeException;

class PublicEnumConstantNotFoundException extends AnalysedCodeException
{
    public function __construct(string $className, string $constantName)
    {
        parent::__construct(\sprintf('No public constant %s found in %s', $constantName, $className));
    }

    public function getTip(): ?string
    {
        return 'Only public constants can be exposed in an Enum';
    }
}
