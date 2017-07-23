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
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

class AuthorTag extends AbstractGenerator implements TagInterface
{
    /**
     * @var string
     */
    protected $authorName;

    /**
     * @var string
     */
    protected $authorEmail;

    /**
     * @param string $authorName
     * @param string $authorEmail
     */
    public function __construct($authorName = null, $authorEmail = null)
    {
        if (! empty($authorName)) {
            $this->setAuthorName($authorName);
        }

        if (! empty($authorEmail)) {
            $this->setAuthorEmail($authorEmail);
        }
    }

    /**
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     */
    public static function fromReflection(ReflectionTagInterface $reflectionTag) : self
    {
        $tagManager = new TagManager();
        $tagManager->initializeDefaultTags();
        return $tagManager->createTagFromReflection($reflectionTag);
    }

    public function getName() : string
    {
        return 'author';
    }

    public function setAuthorEmail(string $authorEmail) : self
    {
        $this->authorEmail = $authorEmail;
        return $this;
    }

    public function getAuthorEmail() : ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorName(string $authorName) : self
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getAuthorName() : ?string
    {
        return $this->authorName;
    }

    public function generate() : string
    {
        return '@author'
            . (! empty($this->authorName) ? ' ' . $this->authorName : '')
            . (! empty($this->authorEmail) ? ' <' . $this->authorEmail . '>' : '');
    }
}
