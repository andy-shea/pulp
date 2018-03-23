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

use Pulp\Binding\Binding;
use Pulp\Injector;

class BindingTest extends \PHPUnit_Framework_TestCase {

  /**
   * @expectedException Pulp\Binding\BindingException
   * @expectedExceptionMessage Cannot bind an interface to itself
   */
  public function testCannotBindInterfaceToItself() {
    $binding = new Binding('TestInterface');
    $binding->to('TestInterface');
  }

  public function testCreateImplementationDependency() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->setMethods(['getInstance'])
        ->getMock();
    $injectorMock->expects($this->once())
           ->method('getInstance')
           ->with($this->equalTo('TestImplementation'));

    $binding = new Binding('TestInterface');
    $binding->to('TestImplementation');
    $binding->createDependency($injectorMock);
  }

  public function testCreateCallableProviderDependency() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->getMock();

    $binding = new Binding('TestInterface');
    $binding->toProvider(function(Injector $injector) {
      return $injector;
    });
    $this->assertSame($injectorMock, $binding->createDependency($injectorMock));

    $binding = new Binding('TestInterface');
    $binding->toProvider([$this, 'mockProvider']);
    $this->assertSame($injectorMock, $binding->createDependency($injectorMock));
  }

  public function mockProvider(Injector $injector) {
    return $injector;
  }

  public function testCreateInstanceDependency() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->getMock();

    $binding = new Binding('TestInterface');
    $instance = new \StdClass();
    $binding->toInstance($instance);
    $this->assertSame($instance, $binding->createDependency($injectorMock));
  }

  public function testCreateInterfaceDependency() {
    $injectorMock = $this->getMockBuilder('Pulp\Injector')
        ->disableOriginalConstructor()
        ->setMethods(['createInstance'])
        ->getMock();
    $injectorMock->expects($this->once())
           ->method('createInstance')
           ->with($this->equalTo('TestInterface'));

    $binding = new Binding('TestInterface');
    $binding->createDependency($injectorMock);
  }

}
