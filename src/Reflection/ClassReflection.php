<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection;

use ReflectionClass;
use Zend\Code\Annotation\AnnotationCollection;
use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Scanner\AnnotationScanner;
use Zend\Code\Scanner\FileScanner;

use function array_shift;
use function array_slice;
use function array_unshift;
use function file;
use function file_exists;
use function implode;
use function strstr;

class ClassReflection extends ReflectionClass implements ReflectionInterface
{
    /**
     * @var AnnotationScanner
     */
    protected $annotations;

    /**
     * @var DocBlockReflection
     */
    protected $docBlock;

    public function getDeclaringFile() : FileReflection
    {
        return new FileReflection($this->getFileName());
    }

    /**
     * Return the classes DocBlock reflection object
     *
     * @return DocBlockReflection|bool
     * @throws Exception\ExceptionInterface for missing DocBock or invalid reflection class
     */
    public function getDocBlock()
    {
        if (null !== $this->docBlock) {
            return $this->docBlock;
        }

        if ('' === $this->getDocComment()) {
            return false;
        }

        $this->docBlock = new DocBlockReflection($this);

        return $this->docBlock;
    }

    /**
     * @param  AnnotationManager $annotationManager
     * @return AnnotationCollection|bool
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        $docComment = $this->getDocComment();

        if ($docComment == '') {
            return false;
        }

        if ($this->annotations) {
            return $this->annotations;
        }

        $fileScanner       = $this->createFileScanner($this->getFileName());
        $nameInformation   = $fileScanner->getClassNameInformation($this->getName());

        if (! $nameInformation) {
            return false;
        }

        $this->annotations = new AnnotationScanner($annotationManager, $docComment, $nameInformation);

        return $this->annotations;
    }

    public function getStartLine(bool $includeDocComment = false) : int
    {
        if ($includeDocComment && $this->getDocComment() != '') {
            return $this->getDocBlock()->getStartLine();
        }

        return parent::getStartLine();
    }

    public function getContents($includeDocBlock = true) : string
    {
        $fileName = $this->getFileName();

        if (false === $fileName || ! file_exists($fileName)) {
            return '';
        }

        $filelines = file($fileName);
        $startnum  = $this->getStartLine($includeDocBlock);
        $endnum    = $this->getEndLine() - $this->getStartLine();

        // Ensure we get between the open and close braces
        $lines = array_slice($filelines, $startnum, $endnum);
        array_unshift($lines, $filelines[$startnum - 1]);

        return strstr(implode('', $lines), '{');
    }

    /**
     * Get all reflection objects of implemented interfaces
     *
     * @return ClassReflection[]
     */
    public function getInterfaces() : array
    {
        return array_values(array_map(
            function (\ReflectionClass $phpReflection) : ClassReflection {
                return new ClassReflection($phpReflection->getName());
            },
            parent::getInterfaces()
        ));
    }

    /**
     * Return method reflection by name
     */
    public function getMethod($name) : MethodReflection
    {
        return new MethodReflection($this->getName(), parent::getMethod($name)->getName());
    }

    /**
     * Get reflection objects of all methods
     *
     * @param  int $filter
     * @return MethodReflection[]
     */
    public function getMethods($filter = -1) : array
    {
        return array_values(array_map(
            function (\ReflectionMethod $method) : MethodReflection {
                return new MethodReflection($this->getName(), $method->getName());
            },
            parent::getMethods($filter)
        ));
    }

    /**
     * Returns an array of reflection classes of traits used by this class.
     */
    public function getTraits() : ?array
    {
        $vals = [];
        $traits = parent::getTraits();
        if ($traits === null) {
            return null;
        }

        foreach ($traits as $trait) {
            $vals[] = new ClassReflection($trait->getName());
        }

        return $vals;
    }

    /**
     * Get parent reflection class of reflected class
     *
     * @return ClassReflection|bool
     */
    public function getParentClass()
    {
        $phpReflection = parent::getParentClass();

        if (! $phpReflection) {
            return false;
        }

        return new ClassReflection($phpReflection->getName());
    }

    /**
     * Return reflection property of this class by name
     *
     * @param  string $name
     */
    public function getProperty($name) : PropertyReflection
    {
        return new PropertyReflection($this->getName(), parent::getProperty($name)->getName());
    }

    /**
     * Return reflection properties of this class
     *
     * @param  int $filter
     * @return PropertyReflection[]
     */
    public function getProperties($filter = -1) : array
    {
        $phpReflections  = parent::getProperties($filter);
        $zendReflections = [];
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance          = new PropertyReflection($this->getName(), $phpReflection->getName());
            $zendReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);

        return $zendReflections;
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return parent::__toString();
    }

    /**
     * Creates a new FileScanner instance.
     *
     * By having this as a separate method it allows the method to be overridden
     * if a different FileScanner is needed.
     */
    protected function createFileScanner(string $filename) : FileScanner
    {
        return new FileScanner($filename);
    }
}
