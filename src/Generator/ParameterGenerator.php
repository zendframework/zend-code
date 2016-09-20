<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\ParameterReflection;

class ParameterGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var TypeGenerator|null
     */
    protected $type = null;

    /**
     * @var string|ValueGenerator
     */
    protected $defaultValue = null;

    /**
     * @var int
     */
    protected $position = null;

    /**
     * @var bool
     */
    protected $passedByReference = false;

    /**
     * @var bool
     */
    private $variadic = false;

    /**
     * @param  ParameterReflection $reflectionParameter
     * @return ParameterGenerator
     */
    public static function fromReflection(ParameterReflection $reflectionParameter)
    {
        $param = new ParameterGenerator();

        $param->setName($reflectionParameter->getName());

        if ($type = self::extractFQCNTypeFromReflectionType($reflectionParameter)) {
            $param->setType($type);
        }

        $param->setPosition($reflectionParameter->getPosition());

        $variadic = method_exists($reflectionParameter, 'isVariadic') && $reflectionParameter->isVariadic();

        $param->setVariadic($variadic);

        if (! $variadic && $reflectionParameter->isOptional()) {
            try {
                $param->setDefaultValue($reflectionParameter->getDefaultValue());
            } catch (\ReflectionException $e) {
                $param->setDefaultValue(null);
            }
        }

        $param->setPassedByReference($reflectionParameter->isPassedByReference());

        return $param;
    }

    /**
     * Generate from array
     *
     * @configkey name              string                                          [required] Class Name
     * @configkey type              string
     * @configkey defaultvalue      null|bool|string|int|float|array|ValueGenerator
     * @configkey passedbyreference bool
     * @configkey position          int
     * @configkey sourcedirty       bool
     * @configkey indentation       string
     * @configkey sourcecontent     string
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return ParameterGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Parameter generator requires that a name is provided for this object'
            );
        }

        $param = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'type':
                    $param->setType($value);
                    break;
                case 'defaultvalue':
                    $param->setDefaultValue($value);
                    break;
                case 'passedbyreference':
                    $param->setPassedByReference($value);
                    break;
                case 'position':
                    $param->setPosition($value);
                    break;
                case 'sourcedirty':
                    $param->setSourceDirty($value);
                    break;
                case 'indentation':
                    $param->setIndentation($value);
                    break;
                case 'sourcecontent':
                    $param->setSourceContent($value);
                    break;
            }
        }

        return $param;
    }

    /**
     * @param  string $name
     * @param  string $type
     * @param  mixed $defaultValue
     * @param  int $position
     * @param  bool $passByReference
     */
    public function __construct(
        $name = null,
        $type = null,
        $defaultValue = null,
        $position = null,
        $passByReference = false
    ) {
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
        if (null !== $position) {
            $this->setPosition($position);
        }
        if (false !== $passByReference) {
            $this->setPassedByReference(true);
        }
    }

    /**
     * @param  string|TypeGenerator $type
     * @throws Exception\InvalidArgumentException
     * @return ParameterGenerator
     */
    public function setType($type)
    {
        if (is_string($type)) {
            $this->type = TypeGenerator::fromTypeString($type);
            return $this;
        }

        if ($type instanceof TypeGenerator) {
            $this->type = $type;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            'Unknown parameter type passed in'
        );
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type
            ? (string) $this->type
            : null;
    }

    /**
     * @param  string $name
     * @return ParameterGenerator
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the default value of the parameter.
     *
     * Certain variables are difficult to express
     *
     * @param  null|bool|string|int|float|array|ValueGenerator $defaultValue
     * @return ParameterGenerator
     */
    public function setDefaultValue($defaultValue)
    {
        if (!($defaultValue instanceof ValueGenerator)) {
            $defaultValue = new ValueGenerator($defaultValue);
        }
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param  int $position
     * @return ParameterGenerator
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function getPassedByReference()
    {
        return $this->passedByReference;
    }

    /**
     * @param  bool $passedByReference
     * @return ParameterGenerator
     */
    public function setPassedByReference($passedByReference)
    {
        $this->passedByReference = (bool) $passedByReference;
        return $this;
    }

    /**
     * @param bool $variadic
     *
     * @return ParameterGenerator
     */
    public function setVariadic($variadic)
    {
        $this->variadic = (bool) $variadic;

        return $this;
    }

    /**
     * @return bool
     */
    public function getVariadic()
    {
        return $this->variadic;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = $this->generateTypeHint();

        if (true === $this->passedByReference) {
            $output .= '&';
        }

        if ($this->variadic) {
            $output .= '... ';
        }

        $output .= '$' . $this->name;

        if ($this->defaultValue !== null) {
            $output .= ' = ';
            if (is_string($this->defaultValue)) {
                $output .= ValueGenerator::escape($this->defaultValue);
            } elseif ($this->defaultValue instanceof ValueGenerator) {
                $this->defaultValue->setOutputMode(ValueGenerator::OUTPUT_SINGLE_LINE);
                $output .= $this->defaultValue;
            } else {
                $output .= $this->defaultValue;
            }
        }

        return $output;
    }

    /**
     * @param ParameterReflection $reflectionParameter
     *
     * @return null|string
     */
    private static function extractFQCNTypeFromReflectionType(ParameterReflection $reflectionParameter)
    {
        if (! method_exists($reflectionParameter, 'getType')) {
            return self::prePhp7ExtractFQCNTypeFromReflectionType($reflectionParameter);
        }

        $type = method_exists($reflectionParameter, 'getType')
            ? $reflectionParameter->getType()
            : null;

        if (! $type) {
            return null;
        }

        $typeString = (string) $type;

        if ('self' === strtolower($typeString)) {
            // exceptional case: `self` must expand to the reflection type declaring class
            return $reflectionParameter->getDeclaringClass()->getName();
        }

        return $typeString;
    }

    /**
     * For ancient PHP versions (yes, you should upgrade to 7.0):
     *
     * @param ParameterReflection $reflectionParameter
     *
     * @return string|null
     */
    private static function prePhp7ExtractFQCNTypeFromReflectionType(ParameterReflection $reflectionParameter)
    {
        if ($reflectionParameter->isCallable()) {
            return 'callable';
        }

        if ($reflectionParameter->isArray()) {
            return 'array';
        }

        if ($class = $reflectionParameter->getClass()) {
            return $class->getName();
        }

        return null;
    }

    /**
     * @param string|null $type
     *
     * @return string
     */
    private function generateTypeHint()
    {
        if (null === $this->type) {
            return '';
        }

        return $this->type->generate() . ' ';
    }
}
