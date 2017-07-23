<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection;

use ReflectionProperty as PhpReflectionProperty;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Scanner\AnnotationScanner;
use Zend\Code\Scanner\CachingFileScanner;
use Zend\Code\Scanner\FileScanner;

/**
 * @todo       implement line numbers
 */
class PropertyReflection extends PhpReflectionProperty implements ReflectionInterface
{
    /**
     * @var AnnotationScanner
     */
    protected $annotations;

    public function getDeclaringClass() : ClassReflection
    {
        return new ClassReflection(parent::getDeclaringClass()->getName());
    }

    /**
     * @return bool|DocBlockReflection
     */
    public function getDocBlock()
    {
        if (! ($docComment = $this->getDocComment())) {
            return false;
        }

        return new DocBlockReflection($docComment);
    }

    /**
     * @param  AnnotationManager $annotationManager
     * @return AnnotationScanner|bool
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        if (null !== $this->annotations) {
            return $this->annotations;
        }

        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        $class              = $this->getDeclaringClass();
        $cachingFileScanner = $this->createFileScanner($class->getFileName());
        $nameInformation    = $cachingFileScanner->getClassNameInformation($class->getName());

        if (! $nameInformation) {
            return false;
        }

        $this->annotations  = new AnnotationScanner($annotationManager, $docComment, $nameInformation);

        return $this->annotations;
    }

    public function toString() : string
    {
        return $this->__toString();
    }

    /**
     * Creates a new FileScanner instance.
     *
     * By having this as a separate method it allows the method to be overridden
     * if a different FileScanner is needed.
     */
    protected function createFileScanner(string $filename) : FileScanner
    {
        return new CachingFileScanner($filename);
    }
}
