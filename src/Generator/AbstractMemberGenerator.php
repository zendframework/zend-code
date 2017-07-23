<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use function is_array;
use function is_string;
use function sprintf;

abstract class AbstractMemberGenerator extends AbstractGenerator
{
    /**#@+
     * @const int Flags for construction usage
     */
    const FLAG_ABSTRACT  = 0x01;
    const FLAG_FINAL     = 0x02;
    const FLAG_STATIC    = 0x04;
    const FLAG_INTERFACE = 0x08;
    const FLAG_PUBLIC    = 0x10;
    const FLAG_PROTECTED = 0x20;
    const FLAG_PRIVATE   = 0x40;
    /**#@-*/

    /**#@+
     * @param const string
     */
    const VISIBILITY_PUBLIC    = 'public';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PRIVATE   = 'private';
    /**#@-*/

    /**
     * @var DocBlockGenerator
     */
    protected $docBlock;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $flags = self::FLAG_PUBLIC;

    /**
     * @param  int|array $flags
     */
    public function setFlags($flags) : self
    {
        if (! is_array($flags)) {
            $this->flags = $flags;

            return $this;
        }

        $this->flags = array_reduce($flags, function ($previousFlags, $flag) {
            return $previousFlags | $flag;
        }, 0x00);

        return $this;
    }

    public function addFlag(int $flag) : self
    {
        $this->setFlags($this->flags | $flag);
        return $this;
    }

    public function removeFlag(int $flag) : self
    {
        $this->setFlags($this->flags & ~$flag);
        return $this;
    }

    public function setAbstract(bool $isAbstract) : self
    {
        return $isAbstract ? $this->addFlag(self::FLAG_ABSTRACT) : $this->removeFlag(self::FLAG_ABSTRACT);
    }

    public function isAbstract() : bool
    {
        return (bool) ($this->flags & self::FLAG_ABSTRACT);
    }

    public function setInterface(bool $isInterface) : self
    {
        return $isInterface ? $this->addFlag(self::FLAG_INTERFACE) : $this->removeFlag(self::FLAG_INTERFACE);
    }

    public function isInterface() : bool
    {
        return (bool) ($this->flags & self::FLAG_INTERFACE);
    }

    public function setFinal(bool $isFinal) : self
    {
        return $isFinal ? $this->addFlag(self::FLAG_FINAL) : $this->removeFlag(self::FLAG_FINAL);
    }

    public function isFinal() : bool
    {
        return (bool) ($this->flags & self::FLAG_FINAL);
    }

    public function setStatic(bool $isStatic) : self
    {
        return $isStatic ? $this->addFlag(self::FLAG_STATIC) : $this->removeFlag(self::FLAG_STATIC);
    }

    public function isStatic() : bool
    {
        return (bool) ($this->flags & self::FLAG_STATIC); // is FLAG_STATIC in flags
    }

    public function setVisibility(string $visibility) : self
    {
        switch ($visibility) {
            case self::VISIBILITY_PUBLIC:
                $this->removeFlag(self::FLAG_PRIVATE | self::FLAG_PROTECTED); // remove both
                $this->addFlag(self::FLAG_PUBLIC);
                break;
            case self::VISIBILITY_PROTECTED:
                $this->removeFlag(self::FLAG_PUBLIC | self::FLAG_PRIVATE); // remove both
                $this->addFlag(self::FLAG_PROTECTED);
                break;
            case self::VISIBILITY_PRIVATE:
                $this->removeFlag(self::FLAG_PUBLIC | self::FLAG_PROTECTED); // remove both
                $this->addFlag(self::FLAG_PRIVATE);
                break;
        }

        return $this;
    }

    public function getVisibility() : string
    {
        switch (true) {
            case $this->flags & self::FLAG_PROTECTED:
                return self::VISIBILITY_PROTECTED;
            case $this->flags & self::FLAG_PRIVATE:
                return self::VISIBILITY_PRIVATE;
            default:
                return self::VISIBILITY_PUBLIC;
        }
    }

    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @param  DocBlockGenerator|string $docBlock
     * @throws Exception\InvalidArgumentException
     */
    public function setDocBlock($docBlock) : self
    {
        if (is_string($docBlock)) {
            $docBlock = new DocBlockGenerator($docBlock);
        } elseif (! $docBlock instanceof DocBlockGenerator) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s is expecting either a string, array or an instance of %s\DocBlockGenerator',
                __METHOD__,
                __NAMESPACE__
            ));
        }

        $this->docBlock = $docBlock;

        return $this;
    }

    public function getDocBlock() : ?DocBlockGenerator
    {
        return $this->docBlock;
    }
}
