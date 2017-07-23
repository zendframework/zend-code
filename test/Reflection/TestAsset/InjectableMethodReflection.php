<?php

namespace ZendTest\Code\Reflection\TestAsset;

use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Scanner\FileScanner;

class InjectableMethodReflection extends MethodReflection
{
    /**
     * @var FileScanner|null
     */
    protected $fileScanner;

    public function setFileScanner(FileScanner $fileScanner)
    {
        $this->fileScanner = $fileScanner;
    }

    protected function createFileScanner(string $filename) : FileScanner
    {
        return $this->fileScanner;
    }
}
