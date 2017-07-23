<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\NameInformation;

use function current;
use function is_string;
use function ltrim;
use function next;
use function reset;
use function trim;

class ParameterScanner
{
    /**
     * @var bool
     */
    protected $isScanned = false;

    /**
     * @var null|ClassScanner
     */
    protected $declaringScannerClass;

    /**
     * @var null|string
     */
    protected $declaringClass;

    /**
     * @var null|MethodScanner
     */
    protected $declaringScannerFunction;

    /**
     * @var null|string
     */
    protected $declaringFunction;

    /**
     * @var null|string
     */
    protected $defaultValue;

    /**
     * @var null|string
     */
    protected $class;

    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var null|int
     */
    protected $position;

    /**
     * @var bool
     */
    protected $isArray = false;

    /**
     * @var bool
     */
    protected $isDefaultValueAvailable = false;

    /**
     * @var bool
     */
    protected $isOptional = false;

    /**
     * @var bool
     */
    protected $isPassedByReference = false;

    /**
     * @var array|null
     */
    protected $tokens;

    /**
     * @var null|NameInformation
     */
    protected $nameInformation;

    /**
     * @param  array $parameterTokens
     * @param  NameInformation $nameInformation
     */
    public function __construct(array $parameterTokens, NameInformation $nameInformation = null)
    {
        $this->tokens          = $parameterTokens;
        $this->nameInformation = $nameInformation;
    }

    public function setDeclaringClass(string $class) : void
    {
        $this->declaringClass = $class;
    }

    public function setDeclaringScannerClass(ClassScanner $scannerClass) : void
    {
        $this->declaringScannerClass = $scannerClass;
    }

    public function setDeclaringFunction(string $function) : void
    {
        $this->declaringFunction = $function;
    }

    public function setDeclaringScannerFunction(MethodScanner $scannerFunction) : void
    {
        $this->declaringScannerFunction = $scannerFunction;
    }

    public function setPosition(int $position) : void
    {
        $this->position = $position;
    }

    protected function scan() : void
    {
        if ($this->isScanned) {
            return;
        }

        $tokens = &$this->tokens;

        reset($tokens);

        SCANNER_TOP:

        $token = current($tokens);

        if (is_string($token)) {
            // check pass by ref
            if ($token === '&') {
                $this->isPassedByReference = true;
                goto SCANNER_CONTINUE;
            }
            if ($token === '=') {
                $this->isOptional              = true;
                $this->isDefaultValueAvailable = true;
                goto SCANNER_CONTINUE;
            }
        } else {
            if ($this->name === null && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                $this->class .= $token[1];
                goto SCANNER_CONTINUE;
            }
            if ($token[0] === T_VARIABLE) {
                $this->name = ltrim($token[1], '$');
                goto SCANNER_CONTINUE;
            }
        }

        if ($this->name !== null) {
            $this->defaultValue .= trim(is_string($token) ? $token : $token[1]);
        }

        SCANNER_CONTINUE:

        if (next($this->tokens) === false) {
            goto SCANNER_END;
        }
        goto SCANNER_TOP;

        SCANNER_END:

        if ($this->class && $this->nameInformation) {
            $this->class = $this->nameInformation->resolveName($this->class);
        }

        $this->isScanned = true;
    }

    public function getDeclaringScannerClass() : ?ClassScanner
    {
        return $this->declaringScannerClass;
    }

    public function getDeclaringClass() : ?string
    {
        return $this->declaringClass;
    }

    public function getDeclaringScannerFunction() : ?MethodScanner
    {
        return $this->declaringScannerFunction;
    }

    public function getDeclaringFunction() : ?string
    {
        return $this->declaringFunction;
    }

    public function getDefaultValue() : ?string
    {
        $this->scan();

        return $this->defaultValue;
    }

    public function getClass() : ?string
    {
        $this->scan();

        return $this->class;
    }

    public function getName() : ?string
    {
        $this->scan();

        return $this->name;
    }

    public function getPosition() : ?int
    {
        $this->scan();

        return $this->position;
    }

    public function isArray() : bool
    {
        $this->scan();

        return $this->isArray;
    }

    public function isDefaultValueAvailable() : bool
    {
        $this->scan();

        return $this->isDefaultValueAvailable;
    }

    public function isOptional() : bool
    {
        $this->scan();

        return $this->isOptional;
    }

    public function isPassedByReference() : bool
    {
        $this->scan();

        return $this->isPassedByReference;
    }
}
