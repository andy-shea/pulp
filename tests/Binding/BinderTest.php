<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Test\Binding;

use Pulp\Binding\Binder;
use Pulp\Module;
use Pulp\AbstractModule;
use Pulp\Meta\Attribute\Provides;
use Pulp\Meta\Attribute\Singleton;
use Pulp\Scope\Scopes;
use Pulp\Binding\Binding;
use Pulp\Provider\ProviderMethod;
use PHPUnit\Framework\TestCase;

class BinderTest extends TestCase {

  public function testBindReturnsBindingObject() {
    $binder = new Binder();
    $this->assertInstanceOf(Binding::class, $binder->bind('TestInterface'));
  }

  public function testModuleInstall() {
    $moduleMock = $this->getMockBuilder(Module::class)
      ->disableOriginalConstructor()
      ->setMethods(['setBinder', 'configure'])
      ->getMock();
    $binder = new Binder();
    $moduleMock->expects($this->once())->method('setBinder');
    $moduleMock->expects($this->once())->method('configure');

    $binder->install($moduleMock);
  }

  public function testSameModuleInstallsOnlyOnce() {
    $moduleMock = $this->getMockBuilder(Module::class)
      ->setMethods(['setBinder', 'configure'])
      ->getMock();
    $binder = new Binder();
    $moduleMock->expects($this->once())->method('setBinder');
    $moduleMock->expects($this->once())->method('configure');

    $binder->install($moduleMock);
    $binder->install($moduleMock);
  }

  public function testRetrievePreviousBinding() {
    $binder = new Binder();
    $binding = $binder->bind('TestInterface');
    $this->assertSame($binding, $binder->getBindingFor('TestInterface'));
  }

  public function testBindsModuleProviderMethod() {
    $bindingMock = $this->getMockBuilder(Binding::class)
      ->disableOriginalConstructor()
      ->setMethods(['toProvider'])
      ->getMock();
    $bindingMock->expects($this->once())
      ->method('toProvider')
      ->with($this->isInstanceOf(ProviderMethod::class));

    $binderStub = $this->getMockBuilder(Binder::class)
      ->setMethods(['bind'])
      ->getMock();
    $binderStub->expects($this->once())
      ->method('bind')
      ->will($this->returnValue($bindingMock));

    $binderStub->install(new TestModule($binderStub));
  }

  public function testBindsModuleSingletonProviderMethod() {
    $bindingMock = $this->getMockBuilder(Binding::class)
      ->disableOriginalConstructor()
      ->setMethods(['toProvider', 'in'])
      ->getMock();
    $bindingMock->expects($this->once())
      ->method('toProvider')
      ->with($this->isInstanceOf(ProviderMethod::class))
      ->will($this->returnValue($bindingMock));
    $bindingMock->expects($this->once())
      ->method('in')
      ->with($this->identicalTo(Scopes::singleton()));

    $binderStub = $this->getMockBuilder(Binder::class)
      ->setMethods(['bind'])
      ->getMock();
    $binderStub->expects($this->once())
      ->method('bind')
      ->will($this->returnValue($bindingMock));

    $binderStub->install(new TestSingletonModule($binderStub));
  }

}

class TestModule extends AbstractModule {

  public function configure() {}

  #[Provides('TestImplementation')]
  public function testProvider() {}

}

class TestSingletonModule extends AbstractModule {

  public function configure() {}

  #[
    Provides('TestImplementation'),
    Singleton
  ]
  public function testProvider() {}

}
