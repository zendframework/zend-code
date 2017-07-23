<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use Zend\Code\Generator\AbstractGenerator;
use Zend\Code\Generic\Prototype\PrototypeGenericInterface;

use function ltrim;

class GenericTag extends AbstractGenerator implements TagInterface, PrototypeGenericInterface
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
     * @param string $name
     * @param string $content
     */
    public function __construct($name = null, $content = null)
    {
        if (! empty($name)) {
            $this->setName($name);
        }

        if (! empty($content)) {
            $this->setContent($content);
        }
    }

    public function setName($name) : self
    {
        $this->name = ltrim($name, '@');
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setContent(string $content) : self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }

    public function generate() : string
    {
        return '@' . $this->name
            . (! empty($this->content) ? ' ' . $this->content : '');
    }
}
