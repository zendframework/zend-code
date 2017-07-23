<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection;

use ReflectionParameter;

use function method_exists;

class ParameterReflection extends ReflectionParameter implements ReflectionInterface
{
    /**
     * @var bool
     */
    protected $isFromMethod = false;

    public function getDeclaringClass() : ClassReflection
    {
        return new ClassReflection(parent::getDeclaringClass()->getName());
    }

    public function getClass() : ?ClassReflection
    {
        $phpReflection = parent::getClass();
        if ($phpReflection === null) {
            return null;
        }

        return new ClassReflection($phpReflection->getName());
    }

    /**
     * Get declaring function reflection object
     *
     * @return FunctionReflection|MethodReflection
     */
    public function getDeclaringFunction()
    {
        $phpReflection = parent::getDeclaringFunction();
        if ($phpReflection instanceof \ReflectionMethod) {
            $zendReflection = new MethodReflection($this->getDeclaringClass()->getName(), $phpReflection->getName());
        } else {
            $zendReflection = new FunctionReflection($phpReflection->getName());
        }
        unset($phpReflection);

        return $zendReflection;
    }

    public function detectType() : ?string
    {
        if (method_exists($this, 'getType')
            && ($type = $this->getType())
            && $type->isBuiltin()
        ) {
            return (string) $type;
        }

        // can be dropped when dropping PHP7 support:
        if ($this->isArray()) {
            return 'array';
        }

        // can be dropped when dropping PHP7 support:
        if ($this->isCallable()) {
            return 'callable';
        }

        if (($class = $this->getClass()) instanceof \ReflectionClass) {
            return $class->getName();
        }

        $docBlock = $this->getDeclaringFunction()->getDocBlock();

        if (! $docBlock instanceof DocBlockReflection) {
            return null;
        }

        $params = $docBlock->getTags('param');

        if (isset($params[$this->getPosition()])) {
            return $params[$this->getPosition()]->getType();
        }

        return null;
    }

    public function toString() : string
    {
        return parent::__toString();
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return parent::__toString();
    }
}
