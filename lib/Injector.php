<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp;

use Doctrine\Common\Annotations\Reader;

/**
 * The `Injector` is the core of Pulp, supplying dependencies to objects based
 * on the graph built by the supplied `Binder`.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class Injector {

  protected $classes = [];
  protected $binder;
  protected $annotationReader;

  public function __construct(Binding\Binder $binder, Reader $annotationReader) {
    $this->binder = $binder;
    $this->annotationReader = $annotationReader;
  }

  public function binder() {
    return $this->binder;
  }

  public function getInstance($className, $assistedParams = false, $isOptional = false) {
    if ($className === 'Octahedron\Pulp\Injector') return $this;
    $binding = $this->binder->getBindingFor($className);
    if ($binding) return $binding->getDependency($this, $assistedParams, $isOptional);
    return $this->createInstance($className, $assistedParams, $isOptional);
  }

  public function injectMembers($object) {
    $className = get_class($object);
    if (!isset($this->classes[$className])) $this->classes[$className] = new Meta\InjectionMetaClass($className, $this->annotationReader);
    $this->injectSetters($object, $this->classes[$className]->injectableSetters());
  }

  protected function injectSetters($object, $setters) {
    foreach ($setters as $setter => $setterInjectionDetails) {
      call_user_func_array([$object, $setter], array_map(function($parameterMeta) {
        return $this->createParameter($parameterMeta);
      }, $setterInjectionDetails));
    }
  }

  // TODO: cache this outside of per-session cache
  // should only be called internally via Binding
  public function createInstance($className, $assistedParams = null, $isOptional = false) {
    if (class_exists($className)) {
      if (!isset($this->classes[$className])) $this->classes[$className] = new Meta\InjectionMetaClass($className, $this->annotationReader);
      $class = new \ReflectionClass($className);

      $object = ($class->getConstructor()) ?
          $class->newInstanceArgs($this->createConstructorParameters($this->classes[$className], $assistedParams)) :
          new $className();

      $this->injectSetters($object, $this->classes[$className]->injectableSetters());
      return $object;
    }
    if (!$isOptional) throw new Binding\BindingException('No binding found for interface "' . $className . '"');
    return null;
  }

  protected function createConstructorParameters(Meta\InjectionMetaClass $metaClass, $assistedParams) {
    if ($metaClass->hasInjectableConstructor()) {
      return array_map(function($parameterMeta) use ($assistedParams) {
        if ($parameterMeta->isAssisted()) {
          if (!isset($assistedParams[$parameterMeta->name()])) {
            if (!$parameterMeta->isOptional()) throw new Binding\BindingException('Missing assisted parameter "' . $parameterMeta->name() . '"');
            else return $parameterMeta->defaultValue();
          }
          return $assistedParams[$parameterMeta->name()];
        }
        return $this->createParameter($parameterMeta);
      }, $metaClass->injectableConstructor());
    }
    else return (array)$assistedParams;
  }

  protected function createParameter(Meta\InjectionParameter $parameterMeta) {
    if ($parameterMeta->isProvider()) return new Provider\ProviderImpl($this, $parameterMeta->provides());
    return $this->getInstance($parameterMeta->type(), false, $parameterMeta->isOptional());
  }

}
