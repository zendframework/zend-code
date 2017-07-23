<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Generator\DocBlock\Tag\TagInterface;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlockReflection;

use function explode;
use function is_array;
use function sprintf;
use function str_replace;
use function strtolower;
use function trim;
use function wordwrap;

class DocBlockGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $shortDescription;

    /**
     * @var string
     */
    protected $longDescription;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var string
     */
    protected $indentation = '';

    /**
     * @var bool
     */
    protected $wordwrap = true;

    protected static $tagManager;

    /**
     * Build a DocBlock generator object from a reflection object
     */
    public static function fromReflection(DocBlockReflection $reflectionDocBlock) : self
    {
        $docBlock = new static();

        $docBlock->setSourceContent($reflectionDocBlock->getContents());
        $docBlock->setSourceDirty(false);

        $docBlock->setShortDescription($reflectionDocBlock->getShortDescription());
        $docBlock->setLongDescription($reflectionDocBlock->getLongDescription());

        foreach ($reflectionDocBlock->getTags() as $tag) {
            $docBlock->setTag(self::getTagManager()->createTagFromReflection($tag));
        }

        return $docBlock;
    }

    /**
     * Generate from array
     *
     * @configkey shortdescription string The short description for this doc block
     * @configkey longdescription  string The long description for this doc block
     * @configkey tags             array
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromArray(array $array) : self
    {
        $docBlock = new static();

        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'shortdescription':
                    $docBlock->setShortDescription($value);
                    break;
                case 'longdescription':
                    $docBlock->setLongDescription($value);
                    break;
                case 'tags':
                    $docBlock->setTags($value);
                    break;
            }
        }

        return $docBlock;
    }

    protected static function getTagManager() : TagManager
    {
        if (null === static::$tagManager) {
            static::$tagManager = new TagManager();
            static::$tagManager->initializeDefaultTags();
        }
        return static::$tagManager;
    }

    public function __construct(?string $shortDescription = null, ?string $longDescription = null, array $tags = [])
    {
        if ($shortDescription) {
            $this->setShortDescription($shortDescription);
        }
        if ($longDescription) {
            $this->setLongDescription($longDescription);
        }
        if (is_array($tags) && $tags) {
            $this->setTags($tags);
        }
    }

    public function setShortDescription(string $shortDescription) : self
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getShortDescription() : ?string
    {
        return $this->shortDescription;
    }

    public function setLongDescription(string $longDescription) : self
    {
        $this->longDescription = $longDescription;
        return $this;
    }

    public function getLongDescription() : ?string
    {
        return $this->longDescription;
    }

    public function setTags(array $tags) : self
    {
        foreach ($tags as $tag) {
            $this->setTag($tag);
        }

        return $this;
    }

    /**
     * @param array|TagInterface $tag
     * @throws Exception\InvalidArgumentException
     */
    public function setTag($tag) : self
    {
        if (is_array($tag)) {
            // use deprecated Tag class for backward compatibility to old array-keys
            $genericTag = new Tag();
            $genericTag->setOptions($tag);
            $tag = $genericTag;
        } elseif (! $tag instanceof TagInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either an array of method options or an instance of %s\DocBlock\Tag\TagInterface',
                __METHOD__,
                __NAMESPACE__
            ));
        }

        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @return TagInterface[]
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    public function setWordWrap(bool $value) : self
    {
        $this->wordwrap = (bool) $value;
        return $this;
    }

    public function getWordWrap() : bool
    {
        return $this->wordwrap;
    }

    public function generate() : string
    {
        if (! $this->isSourceDirty()) {
            return $this->docCommentize(trim($this->getSourceContent()));
        }

        $output = '';
        if (null !== ($sd = $this->getShortDescription())) {
            $output .= $sd . self::LINE_FEED . self::LINE_FEED;
        }
        if (null !== ($ld = $this->getLongDescription())) {
            $output .= $ld . self::LINE_FEED . self::LINE_FEED;
        }

        /* @var $tag GeneratorInterface */
        foreach ($this->getTags() as $tag) {
            $output .= $tag->generate() . self::LINE_FEED;
        }

        return $this->docCommentize(trim($output));
    }

    protected function docCommentize(string $content) : string
    {
        $indent  = $this->getIndentation();
        $output  = $indent . '/**' . self::LINE_FEED;
        $content = $this->getWordWrap() == true ? wordwrap($content, 80, self::LINE_FEED) : $content;
        $lines   = explode(self::LINE_FEED, $content);
        foreach ($lines as $line) {
            $output .= $indent . ' *';
            if ($line) {
                $output .= ' ' . $line;
            }
            $output .= self::LINE_FEED;
        }
        $output .= $indent . ' */' . self::LINE_FEED;

        return $output;
    }
}
