<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Test;

use Pulp\Injector;
use Pulp\Meta\Annotation\Inject;
use Pulp\Meta\Annotation\Assisted;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;

class InjectorTest extends \PHPUnit_Framework_TestCase {

  protected $annotationReader;
  protected $binderMock;

  public function setup() {
    AnnotationRegistry::registerLoader(function($class) {
      $file = __DIR__ . '/../lib/' . str_replace('\\', '/', substr($class, strlen('Pulp\\'))) . '.php';
      if (file_exists($file)) return !!include $file;
    });
    $this->annotationReader = new AnnotationReader();
    $this->binderMock = $this->getMockBuilder('Pulp\Binding\Binder')->setConstructorArgs([$this->annotationReader])->getMock();
  }

  public function testConstructorInject() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance('Pulp\Test\TestConstructorInject');
    $this->assertInstanceOf('Pulp\Test\TestConstructorInject', $object);
    $this->assertInstanceOf('Pulp\Test\Test', $object->test);
  }

  public function testPropertyInject() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance('Pulp\Test\TestPropertyInject');
    $this->assertInstanceOf('Pulp\Test\TestPropertyInject', $object);
    $this->assertInstanceOf('Pulp\Test\Test', $object->test);
  }

  public function testSetterInject() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance('Pulp\Test\TestSetterInject');
    $this->assertInstanceOf('Pulp\Test\TestSetterInject', $object);
    $this->assertInstanceOf('Pulp\Test\Test', $object->test);
  }

  public function testAllInjectTargets() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance('Pulp\Test\TestAllInjectTargets');
    $this->assertInstanceOf('Pulp\Test\TestAllInjectTargets', $object);
    $this->assertInstanceOf('Pulp\Test\Test', $object->test);
    $this->assertInstanceOf('Pulp\Test\Two', $object->two);
    $this->assertInstanceOf('Pulp\Test\Three', $object->three);
  }

  public function testInjectorReturnsItselfWhenGettingInjectorInstance() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->assertSame($injector, $injector->getInstance('Pulp\Injector'));
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage No binding found for interface "MissingClass"
   */
  public function testErrorIfNonOptionalClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('MissingClass');
  }

  public function testNoErrorIfOptionalClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->assertNull($injector->createInstance('MissingClass', null, true));
  }

  public function testNoErrorIfOptionalConstructorParameterClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('Pulp\Test\TestMissingOptionalConstructorInject');
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage No binding found for interface "Pulp\Test\MissingClass"
   */
  public function testErrorIfNonOptionalConstructorParameterClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('Pulp\Test\TestMissingClassConstructorInject');
  }

  public function testNoErrorIfOptionalSetterParameterClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('Pulp\Test\TestMissingOptionalSetterInject');
  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage No binding found for interface "Pulp\Test\MissingClass"
   */
  public function testErrorIfNonOptionalSetterParameterClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('Pulp\Test\TestMissingSetterInject');
  }

  public function testNoErrorIfOptionalPropertyClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('Pulp\Test\TestMissingOptionalPropertyInject');
  }

  public function testAssistedParameter() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $param = 'test';
    $object = $injector->createInstance('Pulp\Test\TestAssistedParamInject', ['assisted' => $param]);
    $this->assertSame($param, $object->assisted);
  }

  public function testSoleAssistedParameter() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $param = 'test';
    $object = $injector->createInstance('Pulp\Test\TestSoleAssistedParamInject', ['assisted' => $param]);
    $this->assertSame($param, $object->assisted);
  }

  /**
   * @expectedException Pulp\Binding\BindingException
   * @expectedExceptionMessage Missing assisted parameter "assisted"
   */
  public function testErrorOnMissingAssistedParameter() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance('Pulp\Test\TestAssistedParamInject');
  }

  public function testAssistedParameterDefault() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance('Pulp\Test\TestAssistedParamDefaultInject');
    $this->assertSame('test', $object->assisted);
  }

  public function testMultipleAssistedParameterDefaults() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $param = 'two';
    $object = $injector->createInstance('Pulp\Test\TestMultipleAssistedParamDefaultInject', ['assistedTwo' => $param]);
    $this->assertSame('test', $object->assisted);
    $this->assertSame($param, $object->assistedTwo);
  }

}

class Test {}
class Two {}
class Three {}

class TestConstructorInject {

  public $test;

  /** @Inject */
  public function __construct(Test $test) {
    $this->test = $test;
  }

}

class TestPropertyInject {

  /** @Inject(Test::class) */
  public $test;

}

class TestSetterInject {

  public $test;

  /** @Inject */
  public function testSetter(Test $test) {
    $this->test = $test;
  }

}

class TestAllInjectTargets {

  public $test;
  public $two;
  /** @Inject(Three::class) */ public $three;

  /** @Inject */
  public function __construct(Test $test) {
    $this->test = $test;
  }

  /** @Inject */
  public function testSetter(Two $two) {
    $this->two = $two;
  }

}

class TestMissingOptionalConstructorInject {

  /** @Inject */
  public function __construct(MissingClass $class = null) {}

}

class TestMissingClassConstructorInject {

  /** @Inject */
  public function __construct(MissingClass $class) {}

}

class TestMissingOptionalSetterInject {

  /** @Inject */
  public function testSetter(MissingClass $class = null) {}

}

class TestMissingSetterInject {

  /** @Inject */
  public function testSetter(MissingClass $class) {}

}

class TestMissingOptionalPropertyInject {

  /** @Inject(MissingClass::class) */
  public $class = null;

}

class TestAssistedParamInject {

  public $assisted;

  /**
   * @Inject
   * @Assisted("assisted")
   */
  public function __construct(Test $class, $assisted) {
    $this->assisted = $assisted;
  }

}

class TestSoleAssistedParamInject {

  public $assisted;

  /**
   * @Inject
   * @Assisted("assisted")
   */
  public function __construct($assisted) {
    $this->assisted = $assisted;
  }

}

class TestAssistedParamDefaultInject {

  public $assisted;

  /**
   * @Inject
   * @Assisted("assisted")
   */
  public function __construct(Test $class, $assisted = 'test') {
    $this->assisted = $assisted;
  }

}

class TestMultipleAssistedParamDefaultInject {

  public $assisted;
  public $assistedTwo;

  /**
   * @Inject
   * @Assisted({"assisted","assistedTwo"})
   */
  public function __construct(Test $class, $assisted = 'test', $assistedTwo = 'two') {
    $this->assisted = $assisted;
    $this->assistedTwo = $assistedTwo;
  }

}
