<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Scanner;

use Zend\Code\Annotation;
use Zend\Code\Exception;
use Zend\Code\NameInformation;

use function is_array;
use function is_numeric;
use function is_string;
use function ltrim;
use function reset;
use function strpos;
use function substr;
use function trim;
use function var_export;

class PropertyScanner implements ScannerInterface
{
    const T_BOOLEAN = 'boolean';
    const T_INTEGER = 'int';
    const T_STRING  = 'string';
    const T_ARRAY   = 'array';
    const T_UNKNOWN = 'unknown';

    /**
     * @var bool
     */
    protected $isScanned = false;

    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var NameInformation
     */
    protected $nameInformation;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ClassScanner
     */
    protected $scannerClass;

    /**
     * @var int
     */
    protected $lineStart;

    /**
     * @var bool
     */
    protected $isProtected = false;

    /**
     * @var bool
     */
    protected $isPublic = true;

    /**
     * @var bool
     */
    protected $isPrivate = false;

    /**
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @var string
     */
    protected $docComment;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $valueType;

    /**
     * Constructor
     *
     * @param array $propertyTokens
     * @param NameInformation $nameInformation
     */
    public function __construct(array $propertyTokens, NameInformation $nameInformation = null)
    {
        $this->tokens = $propertyTokens;
        $this->nameInformation = $nameInformation;
    }

    public function setClass(string $class) : void
    {
        $this->class = $class;
    }

    public function setScannerClass(ClassScanner $scannerClass) : void
    {
        $this->scannerClass = $scannerClass;
    }

    public function getClassScanner() : ?ClassScanner
    {
        return $this->scannerClass;
    }

    public function getName() : ?string
    {
        $this->scan();
        return $this->name;
    }

    public function getValueType() : ?string
    {
        $this->scan();
        return $this->valueType;
    }

    public function isPublic() : bool
    {
        $this->scan();
        return $this->isPublic;
    }

    public function isPrivate() : bool
    {
        $this->scan();
        return $this->isPrivate;
    }

    public function isProtected() : bool
    {
        $this->scan();
        return $this->isProtected;
    }

    public function isStatic() : bool
    {
        $this->scan();
        return $this->isStatic;
    }

    public function getValue() : ?string
    {
        $this->scan();
        return $this->value;
    }

    public function getDocComment() : ?string
    {
        $this->scan();
        return $this->docComment;
    }

    /**
     * @param Annotation\AnnotationManager $annotationManager
     * @return AnnotationScanner|bool
     */
    public function getAnnotations(Annotation\AnnotationManager $annotationManager)
    {
        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        return new AnnotationScanner($annotationManager, $docComment, $this->nameInformation);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        $this->scan();
        return var_export($this, true);
    }

    /**
     * Scan tokens
     *
     * @throws \Zend\Code\Exception\RuntimeException
     */
    protected function scan() : void
    {
        if ($this->isScanned) {
            return;
        }

        if (! $this->tokens) {
            throw new Exception\RuntimeException('No tokens were provided');
        }

        /**
         * Variables & Setup
         */
        $value            = '';
        $concatenateValue = false;

        $tokens = &$this->tokens;
        reset($tokens);

        foreach ($tokens as $token) {
            $tempValue = $token;
            if (! is_string($token)) {
                [$tokenType, $tokenContent] = $token;

                switch ($tokenType) {
                    case T_DOC_COMMENT:
                        if ($this->docComment === null && $this->name === null) {
                            $this->docComment = $tokenContent;
                        }
                        break;

                    case T_VARIABLE:
                        $this->name = ltrim($tokenContent, '$');
                        break;

                    case T_PUBLIC:
                        // use defaults
                        break;

                    case T_PROTECTED:
                        $this->isProtected = true;
                        $this->isPublic = false;
                        break;

                    case T_PRIVATE:
                        $this->isPrivate = true;
                        $this->isPublic = false;
                        break;

                    case T_STATIC:
                        $this->isStatic = true;
                        break;
                    default:
                        $tempValue = trim($tokenContent);
                        break;
                }
            }

            //end value concatenation
            if (! is_array($token) && trim($token) == ';') {
                $concatenateValue = false;
            }

            if (true === $concatenateValue) {
                $value .= $tempValue;
            }

            //start value concatenation
            if (! is_array($token) && trim($token) == '=') {
                $concatenateValue = true;
            }
        }

        $this->valueType = self::T_UNKNOWN;
        if ($value == 'false' || $value == 'true') {
            $this->valueType = self::T_BOOLEAN;
        } elseif (is_numeric($value)) {
            $this->valueType = self::T_INTEGER;
        } elseif (0 === strpos($value, 'array') || 0 === strpos($value, '[')) {
            $this->valueType = self::T_ARRAY;
        } elseif (in_array(substr($value, 0, 1), ['"', '\''], true)) {
            $value = substr($value, 1, -1); // Remove quotes
            $this->valueType = self::T_STRING;
        }

        $this->value = empty($value) ? null : $value;
        $this->isScanned = true;
    }
}
