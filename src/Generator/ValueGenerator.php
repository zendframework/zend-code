<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use ArrayObject as SplArrayObject;
use Zend\Code\Exception\InvalidArgumentException;
use Zend\Stdlib\ArrayObject as StdlibArrayObject;

use function addcslashes;
use function array_keys;
use function array_merge;
use function array_search;
use function count;
use function get_class;
use function get_defined_constants;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_int;
use function max;
use function sprintf;
use function str_repeat;
use function strpos;

class ValueGenerator extends AbstractGenerator
{
    /**#@+
     * Constant values
     */
    const TYPE_AUTO        = 'auto';
    const TYPE_BOOLEAN     = 'boolean';
    const TYPE_BOOL        = 'bool';
    const TYPE_NUMBER      = 'number';
    const TYPE_INTEGER     = 'integer';
    const TYPE_INT         = 'int';
    const TYPE_FLOAT       = 'float';
    const TYPE_DOUBLE      = 'double';
    const TYPE_STRING      = 'string';
    const TYPE_ARRAY       = 'array';
    const TYPE_ARRAY_SHORT = 'array_short';
    const TYPE_ARRAY_LONG  = 'array_long';
    const TYPE_CONSTANT    = 'constant';
    const TYPE_NULL        = 'null';
    const TYPE_OBJECT      = 'object';
    const TYPE_OTHER       = 'other';
    /**#@-*/

    const OUTPUT_MULTIPLE_LINE = 'multipleLine';
    const OUTPUT_SINGLE_LINE   = 'singleLine';

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $type = self::TYPE_AUTO;

    /**
     * @var int
     */
    protected $arrayDepth = 0;

    /**
     * @var string
     */
    protected $outputMode = self::OUTPUT_MULTIPLE_LINE;

    /**
     * @var array
     */
    protected $allowedTypes;

    /**
     * Autodetectable constants
     *
     * @var SplArrayObject|StdlibArrayObject
     */
    protected $constants;

    /**
     * @param mixed       $value
     * @param string      $type
     * @param string      $outputMode
     * @param null|SplArrayObject|StdlibArrayObject $constants
     */
    public function __construct(
        $value = null,
        string $type = self::TYPE_AUTO,
        string $outputMode = self::OUTPUT_MULTIPLE_LINE,
        $constants = null
    ) {
        // strict check is important here if $type = AUTO
        if ($value !== null) {
            $this->setValue($value);
        }
        if ($type !== self::TYPE_AUTO) {
            $this->setType($type);
        }
        if ($outputMode !== self::OUTPUT_MULTIPLE_LINE) {
            $this->setOutputMode($outputMode);
        }
        if ($constants === null) {
            $constants = new SplArrayObject();
        } elseif (! ($constants instanceof SplArrayObject || $constants instanceof StdlibArrayObject)) {
            throw new InvalidArgumentException(
                '$constants must be an instance of ArrayObject or Zend\Stdlib\ArrayObject'
            );
        }
        $this->constants = $constants;
    }

    /**
     * Init constant list by defined and magic constants
     */
    public function initEnvironmentConstants() : void
    {
        $constants   = [
            '__DIR__',
            '__FILE__',
            '__LINE__',
            '__CLASS__',
            '__TRAIT__',
            '__METHOD__',
            '__FUNCTION__',
            '__NAMESPACE__',
            '::',
        ];
        $constants = array_merge($constants, array_keys(get_defined_constants()), $this->constants->getArrayCopy());
        $this->constants->exchangeArray($constants);
    }

    /**
     * Add constant to list
     */
    public function addConstant(string $constant) : self
    {
        $this->constants->append($constant);

        return $this;
    }

    /**
     * Delete constant from constant list
     */
    public function deleteConstant(string $constant) : bool
    {
        if (($index = array_search($constant, $this->constants->getArrayCopy())) !== false) {
            $this->constants->offsetUnset($index);
        }

        return $index !== false;
    }

    /**
     * Return constant list
     *
     * @return SplArrayObject|StdlibArrayObject
     */
    public function getConstants()
    {
        return $this->constants;
    }

    public function isValidConstantType() : bool
    {
        if ($this->type === self::TYPE_AUTO) {
            $type = $this->getAutoDeterminedType($this->value);
        } else {
            $type = $this->type;
        }

        $validConstantTypes = [
            self::TYPE_ARRAY,
            self::TYPE_ARRAY_LONG,
            self::TYPE_ARRAY_SHORT,
            self::TYPE_BOOLEAN,
            self::TYPE_BOOL,
            self::TYPE_NUMBER,
            self::TYPE_INTEGER,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_DOUBLE,
            self::TYPE_STRING,
            self::TYPE_CONSTANT,
            self::TYPE_NULL,
        ];

        return in_array($type, $validConstantTypes);
    }

    /**
     * @param  mixed $value
     */
    public function setValue($value) : self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setArrayDepth(int $arrayDepth) : self
    {
        $this->arrayDepth = $arrayDepth;
        return $this;
    }

    public function getArrayDepth() : int
    {
        return $this->arrayDepth;
    }

    protected function getValidatedType(string $type) : string
    {
        $types = [
            self::TYPE_AUTO,
            self::TYPE_BOOLEAN,
            self::TYPE_BOOL,
            self::TYPE_NUMBER,
            self::TYPE_INTEGER,
            self::TYPE_INT,
            self::TYPE_FLOAT,
            self::TYPE_DOUBLE,
            self::TYPE_STRING,
            self::TYPE_ARRAY,
            self::TYPE_ARRAY_SHORT,
            self::TYPE_ARRAY_LONG,
            self::TYPE_CONSTANT,
            self::TYPE_NULL,
            self::TYPE_OBJECT,
            self::TYPE_OTHER,
        ];

        if (in_array($type, $types)) {
            return $type;
        }

        return self::TYPE_AUTO;
    }

    public function getAutoDeterminedType($value) : string
    {
        switch (gettype($value)) {
            case 'boolean':
                return self::TYPE_BOOLEAN;
            case 'string':
                foreach ($this->constants as $constant) {
                    if (strpos($value, $constant) !== false) {
                        return self::TYPE_CONSTANT;
                    }
                }
                return self::TYPE_STRING;
            case 'double':
            case 'float':
            case 'integer':
                return self::TYPE_NUMBER;
            case 'array':
                return self::TYPE_ARRAY;
            case 'NULL':
                return self::TYPE_NULL;
            case 'object':
            case 'resource':
            case 'unknown type':
            default:
                return self::TYPE_OTHER;
        }
    }

    /**
     * @throws Exception\RuntimeException
     */
    public function generate() : string
    {
        $type = $this->type;

        if ($type != self::TYPE_AUTO) {
            $type = $this->getValidatedType($type);
        }

        $value = $this->value;

        if ($type == self::TYPE_AUTO) {
            $type = $this->getAutoDeterminedType($value);
        }

        $isArrayType = in_array($type, [self::TYPE_ARRAY, self::TYPE_ARRAY_LONG, self::TYPE_ARRAY_SHORT]);

        if ($isArrayType) {
            foreach ($value as &$curValue) {
                if ($curValue instanceof self) {
                    continue;
                }

                if (is_array($curValue)) {
                    $newType = $type;
                } else {
                    $newType = self::TYPE_AUTO;
                }

                $curValue = new self($curValue, $newType, self::OUTPUT_MULTIPLE_LINE, $this->getConstants());
            }
        }

        $output = '';

        switch ($type) {
            case self::TYPE_BOOLEAN:
            case self::TYPE_BOOL:
                $output .= $value ? 'true' : 'false';
                break;
            case self::TYPE_STRING:
                $output .= self::escape($value);
                break;
            case self::TYPE_NULL:
                $output .= 'null';
                break;
            case self::TYPE_NUMBER:
            case self::TYPE_INTEGER:
            case self::TYPE_INT:
            case self::TYPE_FLOAT:
            case self::TYPE_DOUBLE:
            case self::TYPE_CONSTANT:
                $output .= $value;
                break;
            case self::TYPE_ARRAY:
            case self::TYPE_ARRAY_LONG:
            case self::TYPE_ARRAY_SHORT:
                if ($type == self::TYPE_ARRAY_SHORT) {
                    $startArray = '[';
                    $endArray   = ']';
                } else {
                    $startArray = 'array(';
                    $endArray = ')';
                }

                $output .= $startArray;
                if ($this->outputMode == self::OUTPUT_MULTIPLE_LINE) {
                    $output .= self::LINE_FEED . str_repeat($this->indentation, $this->arrayDepth + 1);
                }
                $outputParts = [];
                $noKeyIndex  = 0;
                foreach ($value as $n => $v) {
                    /* @var $v ValueGenerator */
                    $v->setArrayDepth($this->arrayDepth + 1);
                    $partV = $v->generate();
                    $short = false;
                    if (is_int($n)) {
                        if ($n === $noKeyIndex) {
                            $short = true;
                            $noKeyIndex++;
                        } else {
                            $noKeyIndex = max($n + 1, $noKeyIndex);
                        }
                    }

                    if ($short) {
                        $outputParts[] = $partV;
                    } else {
                        $outputParts[] = (is_int($n) ? $n : self::escape($n)) . ' => ' . $partV;
                    }
                }
                $padding = $this->outputMode == self::OUTPUT_MULTIPLE_LINE
                    ? self::LINE_FEED . str_repeat($this->indentation, $this->arrayDepth + 1)
                    : ' ';
                $output .= implode(',' . $padding, $outputParts);
                if ($this->outputMode == self::OUTPUT_MULTIPLE_LINE) {
                    if (count($outputParts) > 0) {
                        $output .= ',';
                    }
                    $output .= self::LINE_FEED . str_repeat($this->indentation, $this->arrayDepth);
                }
                $output .= $endArray;
                break;
            case self::TYPE_OTHER:
            default:
                throw new Exception\RuntimeException(
                    sprintf('Type "%s" is unknown or cannot be used as property default value.', get_class($value))
                );
        }

        return $output;
    }

    /**
     * Quotes value for PHP code.
     *
     * @param  string $input Raw string.
     * @param  bool $quote Whether add surrounding quotes or not.
     * @return string PHP-ready code.
     */
    public static function escape(string $input, bool $quote = true) : string
    {
        $output = addcslashes($input, "\\'");

        // adds quoting strings
        if ($quote) {
            $output = "'" . $output . "'";
        }

        return $output;
    }

    /**
     * @param  string $outputMode
     * @return ValueGenerator
     */
    public function setOutputMode(string $outputMode) : self
    {
        $this->outputMode = $outputMode;
        return $this;
    }

    public function getOutputMode() : string
    {
        return $this->outputMode;
    }

    public function __toString() : string
    {
        return $this->generate();
    }
}
