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
use Pulp\Meta\Attribute\Returns;
use Pulp\Injector;
use Pulp\Assisted\AssistedInjectException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FactoryProviderTest extends TestCase {

  protected $root;

  protected function setUp(): void {
    $this->root = vfsStream::setup('factoryprovider');
    FactoryProvider::setCacheDir(vfsStream::url('factoryprovider') . '\cache');
  }

  public function testCacheDirectoryCreatedIfNonExistant() {
    $this->assertTrue($this->root->hasChild('cache'));
  }

  public function testFactoryOnlyCreatedOnce() {
    $injectorMock = $this->getMockBuilder(Injector::class)
        ->disableOriginalConstructor()
        ->getMock();

    $factoryProvider = new FactoryProvider(TestFactory::class);
    $factoryProvider->initialise($injectorMock);
    $factoryProvider->get();
    $mtime = $this->root->getChild('cache/Pulp_Test_Assisted_TestFactoryImpl.php')->filemtime();
    $factoryProvider->get();
    $this->assertEquals($mtime, $this->root->getChild('cache/Pulp_Test_Assisted_TestFactoryImpl.php')->filemtime());
  }

  public function testFactoryCreateMethod() {
    $injectorMock = $this->getMockBuilder(Injector::class)
        ->disableOriginalConstructor()
        ->setMethods(['getInstance'])
        ->getMock();
    $injectorMock->expects($this->once())
           ->method('getInstance')
           ->with($this->equalTo('TestInterface'));

    $factoryProvider = new FactoryProvider(TestFactory::class);
    $factoryProvider->initialise($injectorMock);
    $factory = $factoryProvider->get();
    $this->assertTrue(method_exists($factory, 'createTestInterface'));
    $factory->createTestInterface();
  }

  public function testFactoryCreateMethodWithAssistedParam() {
    $injectorMock = $this->getMockBuilder(Injector::class)
        ->disableOriginalConstructor()
        ->getMock();

    $factoryProvider = new FactoryProvider(TestFactoryWithAssistedParam::class);
    $factoryProvider->initialise($injectorMock);
    $factory = $factoryProvider->get();
    $this->assertTrue(method_exists($factory, 'createTestInterface'));
    $reflectedClass = new ReflectionClass($factory);
    $reflectedMethod = $reflectedClass->getMethod('createTestInterface');
    $this->assertEquals(3, $reflectedMethod->getNumberOfParameters());
    $reflectedParameters = $reflectedMethod->getParameters();
    $this->assertEquals('param', $reflectedParameters[0]->getName());
    $this->assertEquals(Assisted::class, $reflectedParameters[0]->getType()->getName());
    $this->assertEquals('second', $reflectedParameters[1]->getName());
    $this->assertFalse($reflectedParameters[1]->isDefaultValueAvailable());
    $this->assertEquals('optional', $reflectedParameters[2]->getName());
    $this->assertTrue($reflectedParameters[2]->isDefaultValueAvailable());
  }

  public function testFactoryWithoutReturnsMethod() {
    $injectorMock = $this->getMockBuilder(Injector::class)
        ->disableOriginalConstructor()
        ->getMock();

    $factoryProvider = new FactoryProvider(InvalidFactory::class);
    $factoryProvider->initialise($injectorMock);
    $this->expectException(AssistedInjectException::class, 'Missing #[Returns(...)] attribute in factory interface');
    $factory = $factoryProvider->get();
  }

}

interface TestFactory {

  #[Returns('TestInterface')]
  function createTestInterface();

}

class Assisted {}

interface TestFactoryWithAssistedParam {

  #[Returns('TestInterface')]
  function createTestInterface(Assisted $param, $second, $optional = false);

}

interface InvalidFactory {

  function createTestInterface();

}
