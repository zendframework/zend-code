<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection\DocBlock\Tag;

use Zend\Code\Generic\Prototype\PrototypeGenericInterface;

use function explode;
use function trim;

class GenericTag implements TagInterface, PrototypeGenericInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var null|string
     */
    protected $contentSplitCharacter;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @param  string $contentSplitCharacter
     */
    public function __construct($contentSplitCharacter = ' ')
    {
        $this->contentSplitCharacter = $contentSplitCharacter;
    }

    /**
     * @param  string $tagDocBlockLine
     */
    public function initialize($tagDocBlockLine) : void
    {
        $this->parse($tagDocBlockLine);
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param  string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function returnValue(int $position) : string
    {
        return $this->values[$position];
    }

    /**
     * Serialize to string
     *
     * Required by Reflector
     *
     * @todo   What should this do?
     * @return string
     */
    public function __toString() : string
    {
        return 'DocBlock Tag [ * @' . $this->name . ' ]' . "\n";
    }

    /**
     * @param  string $docBlockLine
     */
    protected function parse($docBlockLine)
    {
        $this->content = trim($docBlockLine);
        $this->values = explode($this->contentSplitCharacter, $docBlockLine);
    }
}
