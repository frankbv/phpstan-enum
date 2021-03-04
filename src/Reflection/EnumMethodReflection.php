<?php
namespace Frank\PhpStan\Reflection;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class EnumMethodReflection implements MethodReflection
{
    private ClassReflection $classReflection;
    private ConstantReflection $constantReflection;
    private string $name;

    public function __construct(ClassReflection $classReflection, string $name)
    {
        $this->classReflection = $classReflection;
        $this->constantReflection = $classReflection->getConstant($name);
        $this->name = $name;

        if (!$this->constantReflection->isPublic()) {
            throw new PublicEnumConstantNotFoundException($classReflection->getName(), $name);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function isPrivate(): bool
    {
        return $this->constantReflection->isPrivate();
    }

    public function isPublic(): bool
    {
        return $this->constantReflection->isPublic();
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->constantReflection->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->constantReflection->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->constantReflection->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    /**
     * @return ParametersAcceptor[]
     */
    public function getVariants(): array
    {
        $parameterReflections = [];
        if (isset($this->classReflection->getMethodTags()[$this->name])) {
            $methodTag = $this->classReflection->getMethodTags()[$this->name];

            foreach ($methodTag->getParameters() as $name => $parameter) {
                $parameterReflections[] = new NativeParameterReflection(
                    $name,
                    $parameter->isOptional(),
                    $parameter->getType(),
                    $parameter->passedByReference(),
                    $parameter->isVariadic(),
                    $parameter->getDefaultValue()
                );
            }
        }

        return [
            new FunctionVariant(
                TemplateTypeMap::createEmpty(),
                TemplateTypeMap::createEmpty(),
                $parameterReflections,
                false,
                new ObjectType($this->classReflection->getName())
            ),
        ];
    }
}
