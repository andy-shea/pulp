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
use Pulp\Binding\Binder;
use Pulp\Binding\BindingException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase {

  protected $annotationReader;
  protected $binderMock;

  public function setup(): void {
    AnnotationRegistry::registerLoader(function($class) {
      $file = __DIR__ . '/../lib/' . str_replace('\\', '/', substr($class, strlen('Pulp\\'))) . '.php';
      if (file_exists($file)) return !!include $file;
    });
    $this->annotationReader = new AnnotationReader();
    $this->binderMock = $this->getMockBuilder(Binder::class)->setConstructorArgs([$this->annotationReader])->getMock();
  }

  public function testConstructorInject() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance(TestConstructorInject::class);
    $this->assertInstanceOf(TestConstructorInject::class, $object);
    $this->assertInstanceOf(Test::class, $object->test);
  }

  public function testPropertyInject() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance(TestPropertyInject::class);
    $this->assertInstanceOf(TestPropertyInject::class, $object);
    $this->assertInstanceOf(Test::class, $object->test);
  }

  public function testSetterInject() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance(TestSetterInject::class);
    $this->assertInstanceOf(TestSetterInject::class, $object);
    $this->assertInstanceOf(Test::class, $object->test);
  }

  public function testAllInjectTargets() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance(TestAllInjectTargets::class);
    $this->assertInstanceOf(TestAllInjectTargets::class, $object);
    $this->assertInstanceOf(Test::class, $object->test);
    $this->assertInstanceOf('Pulp\Test\Two', $object->two);
    $this->assertInstanceOf('Pulp\Test\Three', $object->three);
  }

  public function testInjectorReturnsItselfWhenGettingInjectorInstance() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->assertSame($injector, $injector->getInstance(Injector::class));
  }

  public function testErrorIfNonOptionalClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->expectException(\Exception::class, 'No binding found for interface "MissingClass"');
    $injector->createInstance('MissingClass');
  }

  public function testNoErrorIfOptionalClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->assertNull($injector->createInstance('MissingClass', null, true));
  }

  public function testNoErrorIfOptionalConstructorParameterClassMissing() {
    $this->expectNotToPerformAssertions();
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance(TestMissingOptionalConstructorInject::class);
  }

  public function testErrorIfNonOptionalConstructorParameterClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->expectException(\Exception::class, 'No binding found for interface "Pulp\Test\MissingClass"');
    $injector->createInstance(TestMissingClassConstructorInject::class);
  }

  public function testNoErrorIfOptionalSetterParameterClassMissing() {
    $this->expectNotToPerformAssertions();
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance(TestMissingOptionalSetterInject::class);
  }

  public function testErrorIfNonOptionalSetterParameterClassMissing() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->expectException(\Exception::class, 'No binding found for interface "Pulp\Test\MissingClass"');
    $injector->createInstance(TestMissingSetterInject::class);
  }

  public function testNoErrorIfOptionalPropertyClassMissing() {
    $this->expectNotToPerformAssertions();
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $injector->createInstance(TestMissingOptionalPropertyInject::class);
  }

  public function testAssistedParameter() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $param = 'test';
    $object = $injector->createInstance(TestAssistedParamInject::class, ['assisted' => $param]);
    $this->assertSame($param, $object->assisted);
  }

  public function testSoleAssistedParameter() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $param = 'test';
    $object = $injector->createInstance(TestSoleAssistedParamInject::class, ['assisted' => $param]);
    $this->assertSame($param, $object->assisted);
  }

  public function testErrorOnMissingAssistedParameter() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $this->expectException(BindingException::class, 'Missing assisted parameter "assisted"');
    $injector->createInstance(TestAssistedParamInject::class);
  }

  public function testAssistedParameterDefault() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $object = $injector->createInstance(TestAssistedParamDefaultInject::class);
    $this->assertSame('test', $object->assisted);
  }

  public function testMultipleAssistedParameterDefaults() {
    $injector = new Injector($this->binderMock, $this->annotationReader);
    $param = 'two';
    $object = $injector->createInstance(TestMultipleAssistedParamDefaultInject::class, ['assistedTwo' => $param]);
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
