<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Test\Assisted;

use Pulp\Assisted\FactoryProvider;
use Pulp\Meta\Annotation\Returns;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use org\bovigo\vfs\vfsStream;

class FactoryProviderTest extends \PHPUnit_Framework_TestCase {

  protected $root;

  protected function setUp() {
    $this->root = vfsStream::setup('factoryprovider');
    AnnotationRegistry::registerLoader(function($class) {
      $file = __DIR__ . '/../../lib/' . str_replace('\\', '/', substr($class, strlen('Pulp\\'))) . '.php';
      if (file_exists($file)) return !!include $file;
    });
    FactoryProvider::setCacheDir(vfsStream::url('factoryprovider') . '\cache');
  }

  public function testCacheDirectoryCreatedIfNonExistant() {
    $this->assertTrue($this->root->hasChild('cache'));
  }

  public function testFactoryOnlyCreatedOnce() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->getMock();

    $factoryProvider = new FactoryProvider('Pulp\Test\Assisted\TestFactory');
    $factoryProvider->setAnnotationReader(new AnnotationReader());
    $factoryProvider->initialise($injectorMock);
    $factoryProvider->get();
    $mtime = $this->root->getChild('cache/Pulp_Test_Assisted_TestFactoryImpl.php')->filemtime();
    $factoryProvider->get();
    $this->assertEquals($mtime, $this->root->getChild('cache/Pulp_Test_Assisted_TestFactoryImpl.php')->filemtime());
  }

  public function testFactoryCreateMethod() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->setMethods(['getInstance'])
        ->getMock();
    $injectorMock->expects($this->once())
           ->method('getInstance')
           ->with($this->equalTo('TestInterface'));

    $factoryProvider = new FactoryProvider('Pulp\Test\Assisted\TestFactory');
    $factoryProvider->setAnnotationReader(new AnnotationReader());
    $factoryProvider->initialise($injectorMock);
    $factory = $factoryProvider->get();
    $this->assertTrue(method_exists($factory, 'createTestInterface'));
    $factory->createTestInterface();
  }

  public function testFactoryCreateMethodWithAssistedParam() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->getMock();

    $factoryProvider = new FactoryProvider('Pulp\Test\Assisted\TestFactoryWithAssistedParam');
    $factoryProvider->setAnnotationReader(new AnnotationReader());
    $factoryProvider->initialise($injectorMock);
    $factory = $factoryProvider->get();
    $this->assertTrue(method_exists($factory, 'createTestInterface'));
    $reflectedClass = new \ReflectionClass($factory);
    $reflectedMethod = $reflectedClass->getMethod('createTestInterface');
    $this->assertEquals(3, $reflectedMethod->getNumberOfParameters());
    $reflectedParameters = $reflectedMethod->getParameters();
    $this->assertEquals('param', $reflectedParameters[0]->getName());
    $this->assertEquals('Pulp\Test\Assisted\Assisted', $reflectedParameters[0]->getClass()->getName());
    $this->assertEquals('second', $reflectedParameters[1]->getName());
    $this->assertFalse($reflectedParameters[1]->isDefaultValueAvailable());
    $this->assertEquals('optional', $reflectedParameters[2]->getName());
    $this->assertTrue($reflectedParameters[2]->isDefaultValueAvailable());
  }

  /**
   * @expectedException Pulp\Assisted\AssistedInjectException
   * @expectedExceptionMessage Missing @Returns annotation in factory interface
   */
  public function testFactoryWithoutReturnsMethod() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->getMock();

    $factoryProvider = new FactoryProvider('Pulp\Test\Assisted\InvalidFactory');
    $factoryProvider->setAnnotationReader(new AnnotationReader());
    $factoryProvider->initialise($injectorMock);
    $factory = $factoryProvider->get();
  }

}

interface TestFactory {

  /** @Returns("TestInterface") */
  function createTestInterface();

}

class Assisted {}

interface TestFactoryWithAssistedParam {

  /** @Returns("TestInterface") */
  function createTestInterface(Assisted $param, $second, $optional = false);

}

interface InvalidFactory {

  function createTestInterface();

}
