<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use Zend\Code\Annotation\AnnotationManager;
use Zend\Code\Annotation\Parser\GenericAnnotationParser;
use Zend\Code\NameInformation;
use Zend\Code\Scanner\AnnotationScanner;

class AnnotationScannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider newLine
     *
     * @param string $newLine
     */
    public function testScannerWorks($newLine)
    {
        $annotationManager = new AnnotationManager();
        $parser = new GenericAnnotationParser();
        $parser->registerAnnotations([
            $foo = new TestAsset\Annotation\Foo(),
            $bar = new TestAsset\Annotation\Bar(),
        ]);
        $annotationManager->attach($parser);

        $docComment = '/**' . $newLine
            . ' * @Test\Foo(\'anything I want()' . $newLine
            . ' * to be\')' . $newLine
            . ' * @Test\Bar' . $newLine . ' */';

        $nameInfo = new NameInformation();
        $nameInfo->addUse('ZendTest\Code\Scanner\TestAsset\Annotation', 'Test');

        $annotationScanner = new AnnotationScanner($annotationManager, $docComment, $nameInfo);
        $this->assertEquals(get_class($foo), get_class($annotationScanner[0]));
        $this->assertEquals("'anything I want()\n to be'", $annotationScanner[0]->getContent());
        $this->assertEquals(get_class($bar), get_class($annotationScanner[1]));
    }

    public function newLine()
    {
        return [
            ["\n"],
            ["\r"],
            ["\r\n"],
        ];
    }
}
