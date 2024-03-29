<?php
namespace Frank\Test\PhpStan\Reflection;

use Frank\PhpStan\Reflection\EnumMethodReflection;
use Frank\PhpStan\Reflection\EnumMethodsClassReflectionExtension;
use Frank\PhpStan\Reflection\PublicEnumConstantNotFoundException;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\VerbosityLevel;

class EnumReflectionTest extends PHPStanTestCase
{
    private ReflectionProvider $reflectionProvider;
    private EnumMethodsClassReflectionExtension $reflectionExtension;

    public function setUp(): void
    {
        $this->reflectionProvider = $this->createReflectionProvider();
        $this->reflectionExtension = new EnumMethodsClassReflectionExtension();
    }

    /**
     * @dataProvider methodNameProvider
     */
    public function testExistingEnumMethods(string $methodName): void
    {
        $classReflection = $this->reflectionProvider->getClass(TestEnum::class);
        $this->assertTrue($this->reflectionExtension->hasMethod($classReflection, $methodName));
    }

    /**
     * @return string[][]
     */
    public function methodNameProvider(): array
    {
        return [
            ['PUBLIC_CONST'],
            ['PROTECTED_CONST'],
            ['PRIVATE_CONST'],
        ];
    }

    public function testNonExistingEnumMethod(): void
    {
        $classReflection = $this->reflectionProvider->getClass(TestEnum::class);
        $this->assertFalse($this->reflectionExtension->hasMethod($classReflection, 'DO_NOT_EXIST'));
    }

    public function testGetEnumMethodReflectionOfPublicConst(): void
    {
        $classReflection = $this->reflectionProvider->getClass(TestEnum::class);

        $this->assertInstanceOf(
            EnumMethodReflection::class,
            $this->reflectionExtension->getMethod($classReflection, 'PUBLIC_CONST')
        );
    }

    /**
     * @dataProvider nonPublicMethodNameProvider
     */
    public function testGetEnumMethodReflectionOfNonPublicConsts(string $methodName): void
    {
        $this->expectException(PublicEnumConstantNotFoundException::class);

        $classReflection = $this->reflectionProvider->getClass(TestEnum::class);

        $this->assertInstanceOf(
            EnumMethodReflection::class,
            $methodReflection = $this->reflectionExtension->getMethod($classReflection, $methodName)
        );
    }

    /**
     * @return string[][]
     */
    public function nonPublicMethodNameProvider(): array
    {
        return [
            ['PROTECTED_CONST'],
            ['PRIVATE_CONST'],
        ];
    }

    public function testEnumMethodProperties(): void
    {
        $classReflection = $this->reflectionProvider->getClass(TestEnum::class);
        $methodReflection = $this->reflectionExtension->getMethod($classReflection, 'PUBLIC_CONST');
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        $this->assertSame('PUBLIC_CONST', $methodReflection->getName());
        $this->assertSame($classReflection, $methodReflection->getDeclaringClass());
        $this->assertTrue($methodReflection->isStatic());
        $this->assertFalse($methodReflection->isPrivate());
        $this->assertTrue($methodReflection->isPublic());
        $this->assertSame(TestEnum::class, $parametersAcceptor->getReturnType()->describe(VerbosityLevel::value()));
    }
}
