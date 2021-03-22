<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp;

use Pulp\Binding\Binder;
use Pulp\Meta\InjectionMetaClass;
use Pulp\Meta\InjectionParameter;
use Pulp\Provider\ProviderImpl;
use Pulp\Binding\BindingException;
use ReflectionObject;
use ReflectionClass;

/**
 * The `Injector` is the core of Pulp, supplying dependencies to objects based
 * on the graph built by the supplied `Binder`.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class Injector {

  protected array $classes = [];
  protected Binder $binder;

  public function __construct(Binder $binder) {
    $this->binder = $binder;
  }

  public function binder(): Binder {
    return $this->binder;
  }

  public function getInstance(string $className, array $assistedParams = null, bool $isOptional = false): mixed {
    if ($className === __CLASS__) return $this;
    $binding = $this->binder->getBindingFor($className);
    if ($binding) return $binding->getDependency($this, $assistedParams, $isOptional);
    return $this->createInstance($className, $assistedParams, $isOptional);
  }

  public function injectMembers(object $object): void {
    $className = get_class($object);
    if (!isset($this->classes[$className])) {
      $this->classes[$className] = new InjectionMetaClass($className);
    }
    $this->injectProperties($object, $this->classes[$className]->injectableProperties());
    $this->injectSetters($object, $this->classes[$className]->injectableSetters());
  }

  protected function injectProperties(object $object, array $properties): void {
    $reflectedObject = new ReflectionObject($object);
    foreach ($properties as $property => $parameterMeta) {
      $reflectedProperty = $reflectedObject->getProperty($property);
      $value = $this->createParameter($parameterMeta);
      if ($reflectedProperty->isPublic()) $object->$property = $value;
      else {
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($object, $value);
      }
    }
  }

  protected function injectSetters(object $object, array $setters): void {
    foreach ($setters as $setter => $setterInjectionDetails) {
      call_user_func_array([$object, $setter], array_map(
        fn(InjectionParameter $parameterMeta): mixed => $this->createParameter($parameterMeta),
        $setterInjectionDetails
      ));
    }
  }

  // TODO: cache this outside of per-session cache
  // should only be called internally via Binding
  public function createInstance(string $className, array $assistedParams = null, bool $isOptional = false): mixed {
    if (class_exists($className)) {
      if (!isset($this->classes[$className])) {
        $this->classes[$className] = new InjectionMetaClass($className);
      }
      $class = new ReflectionClass($className);
      $object = ($class->getConstructor())
        ? $class->newInstanceArgs(
          array_values($this->createConstructorParameters($this->classes[$className], $assistedParams))
        )
        : new $className();
      $this->injectMembers($object);
      return $object;
    }
    if (!$isOptional) throw new BindingException('No binding found for interface "' . $className . '"');
    return null;
  }

  protected function createConstructorParameters(InjectionMetaClass $metaClass, ?array $assistedParams = null): array {
    if ($metaClass->hasInjectableConstructor()) {
      return array_map(function(InjectionParameter $parameterMeta) use ($assistedParams) {
        if ($parameterMeta->isAssisted()) {
          if (!isset($assistedParams[$parameterMeta->name()])) {
            if (!$parameterMeta->isOptional()) {
              throw new BindingException('Missing assisted parameter "' . $parameterMeta->name() . '"');
            }
            else return $parameterMeta->defaultValue();
          }
          return $assistedParams[$parameterMeta->name()];
        }
        return $this->createParameter($parameterMeta);
      }, $metaClass->injectableConstructor());
    }
    else return (array)$assistedParams;
  }

  protected function createParameter(InjectionParameter $parameterMeta): mixed {
    if ($parameterMeta->isProvider()) return new ProviderImpl($this, $parameterMeta->provides());
    return $this->getInstance($parameterMeta->type(), null, $parameterMeta->isOptional());
  }

}
