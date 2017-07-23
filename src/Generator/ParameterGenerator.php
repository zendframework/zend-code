<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use ReflectionParameter;
use Zend\Code\Reflection\ParameterReflection;

use function is_string;
use function method_exists;
use function str_replace;
use function strtolower;

class ParameterGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var TypeGenerator|null
     */
    protected $type;

    /**
     * @var string|ValueGenerator
     */
    protected $defaultValue;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var bool
     */
    protected $passedByReference = false;

    /**
     * @var bool
     */
    private $variadic = false;

    public static function fromReflection(ParameterReflection $reflectionParameter) : self
    {
        $param = new ParameterGenerator();

        $param->setName($reflectionParameter->getName());

        if ($type = self::extractFQCNTypeFromReflectionType($reflectionParameter)) {
            $param->setType($type);
        }

        $param->setPosition($reflectionParameter->getPosition());

        $variadic = method_exists($reflectionParameter, 'isVariadic') && $reflectionParameter->isVariadic();

        $param->setVariadic($variadic);

        if (! $variadic && ($reflectionParameter->isOptional() || $reflectionParameter->isDefaultValueAvailable())) {
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
     */
    public static function fromArray(array $array) : self
    {
        if (! isset($array['name'])) {
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
        ?string $name = null,
        ?string $type = null,
        $defaultValue = null,
        ?int $position = null,
        bool $passByReference = false
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

    public function setType(string $type) : self
    {
        $this->type = TypeGenerator::fromTypeString($type);

        return $this;
    }

    public function getType() : ?string
    {
        return $this->type
            ? (string) $this->type
            : null;
    }

    public function setName(string $name) : self
    {
        $this->name = (string) $name;
        return $this;
    }

    public function getName() : ?string
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
    public function setDefaultValue($defaultValue) : self
    {
        if (! $defaultValue instanceof ValueGenerator) {
            $defaultValue = new ValueGenerator($defaultValue);
        }
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function getDefaultValue() : ?ValueGenerator
    {
        return $this->defaultValue;
    }

    public function setPosition(int $position) : self
    {
        $this->position = $position;
        return $this;
    }

    public function getPosition() : ?int
    {
        return $this->position;
    }

    public function getPassedByReference() : bool
    {
        return $this->passedByReference;
    }

    public function setPassedByReference(bool $passedByReference) : self
    {
        $this->passedByReference = $passedByReference;
        return $this;
    }

    public function setVariadic(bool $variadic) : self
    {
        $this->variadic = $variadic;

        return $this;
    }

    public function getVariadic() : bool
    {
        return $this->variadic;
    }

    public function generate() : string
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

    private static function extractFQCNTypeFromReflectionType(ParameterReflection $reflectionParameter) : ?string
    {
        $type = method_exists($reflectionParameter, 'getType')
            ? $reflectionParameter->getType()
            : null;

        if (! $type) {
            return null;
        }

        if (! method_exists($type, 'getName')) {
            return self::expandLiteralParameterType((string) $type, $reflectionParameter);
        }

        return ($type->allowsNull() ? '?' : '')
            . self::expandLiteralParameterType($type->getName(), $reflectionParameter);
    }

    /**
     * @param string              $literalParameterType
     * @param ReflectionParameter $reflectionParameter
     *
     * @return string
     */
    private static function expandLiteralParameterType(
        string $literalParameterType,
        ReflectionParameter $reflectionParameter
    ) : string {
        if ('self' === strtolower($literalParameterType)) {
            return $reflectionParameter->getDeclaringClass()->getName();
        }

        if ('parent' === strtolower($literalParameterType)) {
            return $reflectionParameter->getDeclaringClass()->getParentClass()->getName();
        }

        return $literalParameterType;
    }

    private function generateTypeHint() : string
    {
        if (null === $this->type) {
            return '';
        }

        return $this->type->generate() . ' ';
    }
}
