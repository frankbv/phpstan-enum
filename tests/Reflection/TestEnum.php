<?php
namespace Frank\Test\PhpStan\Reflection;

use Frank\Enum;

class TestEnum extends Enum
{
    public const PUBLIC_CONST = 'public';
    protected const PROTECTED_CONST = 'protected';
    private const PRIVATE_CONST = 'private';
}

