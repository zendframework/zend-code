<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Reflection\MethodReflection;

class MethodGenerator extends AbstractMemberGenerator
{
    /**
     * @var DocBlockGenerator
     */
    protected $docBlock = null;

    /**
     * @var ParameterGenerator[]
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $body = null;

    /**
     * @var null|TypeGenerator
     */
    private $returnType;

    /**
     * @var bool
     */
    private $returnsReference = false;

    /**
     * @param  MethodReflection $reflectionMethod
     * @return MethodGenerator
     */
    public static function fromReflection(MethodReflection $reflectionMethod)
    {
        $method         = new static();
        $declaringClass = $reflectionMethod->getDeclaringClass();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);
        $method->setReturnType(self::extractReturnTypeFromMethodReflection($reflectionMethod));

        if ($reflectionMethod->getDocComment() != '') {
            $method->setDocBlock(DocBlockGenerator::fromReflection($reflectionMethod->getDocBlock()));
        }

        $method->setFinal($reflectionMethod->isFinal());

        if ($reflectionMethod->isPrivate()) {
            $method->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionMethod->isProtected()) {
            $method->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $method->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $method->setInterface($declaringClass->isInterface());
        $method->setStatic($reflectionMethod->isStatic());
        $method->setReturnsReference($reflectionMethod->returnsReference());
        $method->setName($reflectionMethod->getName());

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(ParameterGenerator::fromReflection($reflectionParameter));
        }

        $method->setBody(static::clearBodyIndention($reflectionMethod->getBody()));

        return $method;
    }

    /**
     * Identify the space indention from the first line and remove this indention
     * from all lines
     *
     * @param string $body
     *
     * @return string
     */
    protected static function clearBodyIndention($body)
    {
        if (empty($body)) {
            return $body;
        }

        $lines = explode(PHP_EOL, $body);
        
        if (count($lines) > 1) {
            $indention = str_replace(trim($lines[1]), '', $lines[1]);
    
            foreach ($lines as $key => $line) {
                if (substr($line, 0, strlen($indention)) == $indention) {
                    $lines[$key] = substr($line, strlen($indention));
                }
            }
        }

        $body = implode(PHP_EOL, $lines);

        return $body;
    }

    /**
     * Generate from array
     *
     * @configkey name           string        [required] Class Name
     * @configkey docblock       string        The docblock information
     * @configkey flags          int           Flags, one of MethodGenerator::FLAG_ABSTRACT MethodGenerator::FLAG_FINAL
     * @configkey parameters     string        Class which this class is extending
     * @configkey body           string
     * @configkey abstract       bool
     * @configkey final          bool
     * @configkey static         bool
     * @configkey visibility     string
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return MethodGenerator
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Method generator requires that a name is provided for this object'
            );
        }

        $method = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'docblock':
                    $docBlock = ($value instanceof DocBlockGenerator) ? $value : DocBlockGenerator::fromArray($value);
                    $method->setDocBlock($docBlock);
                    break;
                case 'flags':
                    $method->setFlags($value);
                    break;
                case 'parameters':
                    $method->setParameters($value);
                    break;
                case 'body':
                    $method->setBody($value);
                    break;
                case 'abstract':
                    $method->setAbstract($value);
                    break;
                case 'final':
                    $method->setFinal($value);
                    break;
                case 'interface':
                    $method->setInterface($value);
                    break;
                case 'static':
                    $method->setStatic($value);
                    break;
                case 'visibility':
                    $method->setVisibility($value);
                    break;
            }
        }

        return $method;
    }

    /**
     * @param  string $name
     * @param  array $parameters
     * @param  int $flags
     * @param  string $body
     * @param  DocBlockGenerator|string $docBlock
     */
    public function __construct(
        $name = null,
        array $parameters = [],
        $flags = self::FLAG_PUBLIC,
        $body = null,
        $docBlock = null
    ) {
        if ($name) {
            $this->setName($name);
        }
        if ($parameters) {
            $this->setParameters($parameters);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
        if ($body) {
            $this->setBody($body);
        }
        if ($docBlock) {
            $this->setDocBlock($docBlock);
        }
    }

    /**
     * @param  array $parameters
     * @return MethodGenerator
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->setParameter($parameter);
        }

        return $this;
    }

    /**
     * @param  ParameterGenerator|array|string $parameter
     * @throws Exception\InvalidArgumentException
     * @return MethodGenerator
     */
    public function setParameter($parameter)
    {
        if (is_string($parameter)) {
            $parameter = new ParameterGenerator($parameter);
        }

        if (is_array($parameter)) {
            $parameter = ParameterGenerator::fromArray($parameter);
        }

        if (!$parameter instanceof ParameterGenerator) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s is expecting either a string, array or an instance of %s\ParameterGenerator',
                __METHOD__,
                __NAMESPACE__
            ));
        }

        $this->parameters[$parameter->getName()] = $parameter;

        return $this;
    }

    /**
     * @return ParameterGenerator[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param  string $body
     * @return MethodGenerator
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string|null
     *
     * @return MethodGenerator
     */
    public function setReturnType($returnType = null)
    {
        $this->returnType = null === $returnType
            ? null
            : TypeGenerator::fromTypeString($returnType);

        return $this;
    }

    /**
     * @param bool $returnsReference
     *
     * @return MethodGenerator
     */
    public function setReturnsReference($returnsReference)
    {
        $this->returnsReference = (bool) $returnsReference;

        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '';

        $indent = $this->getIndentation();

        if (($docBlock = $this->getDocBlock()) !== null) {
            $docBlock->setIndentation($indent);
            $output .= $docBlock->generate();
        }

        $output .= $indent;

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        } else {
            $output .= (($this->isFinal()) ? 'final ' : '');
        }

        $output .= $this->getVisibility()
            . (($this->isStatic()) ? ' static' : '')
            . ' function '
            . ($this->returnsReference ? '& ' : '')
            . $this->getName() . '(';

        $parameters = $this->getParameters();
        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                $parameterOutput[] = $parameter->generate();
            }

            $output .= implode(', ', $parameterOutput);
        }

        $output .= ')';

        if ($this->returnType) {
            $output .= ' : ' . $this->returnType->generate();
        }

        if ($this->isAbstract()) {
            return $output . ';';
        }

        if ($this->isInterface()) {
            return $output . ';';
        }

        $output .= self::LINE_FEED . $indent . '{' . self::LINE_FEED;

        if ($this->body) {
            $output .= preg_replace('#^((?![a-zA-Z0-9_-]+;).+?)$#m', $indent . $indent . '$1', trim($this->body))
                . self::LINE_FEED;
        }

        $output .= $indent . '}' . self::LINE_FEED;

        return $output;
    }

    public function __toString()
    {
        return $this->generate();
    }

    /**
     * @param MethodReflection $methodReflection
     *
     * @return null|string
     */
    private static function extractReturnTypeFromMethodReflection(MethodReflection $methodReflection)
    {
        $returnType = method_exists($methodReflection, 'getReturnType')
            ? $methodReflection->getReturnType()
            : null;

        if (! $returnType) {
            return null;
        }

        $returnTypeString = (string) $returnType;

        if ('self' === strtolower($returnType)) {
            return $methodReflection->getDeclaringClass()->getName();
        }

        return $returnTypeString;
    }
}
