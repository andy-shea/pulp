<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Binding;

use Pulp\Module;
use Pulp\Provider\ProviderMethod;
use Pulp\Scope\Scopes;
use Pulp\Assisted\FactoryProvider;
use Pulp\Meta\Annotation\Provides;
use Pulp\Meta\Annotation\Singleton;
use Doctrine\Common\Annotations\Reader;

/**
 * Collects binding configurations used to resolve dependency graphs.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class Binder {

  protected $annotationReader;
  protected $bindings;
  protected $modules = [];

  public function __construct(Reader $annotationReader) {
    $this->annotationReader = $annotationReader;
  }

  public function install(Module $module) {
    if (!isset($this->modules[spl_object_hash($module)])) {
      $module->setBinder($this);
      $module->configure();
      $this->getProviderMethods($module);
      $this->modules[spl_object_hash($module)] = true;
    }
  }

  public function bind($interface) {
    $binding = new Binding($interface);
    $this->bindings[$interface] = $binding;
    return $binding;
  }

  public function installFactoryProvider(FactoryProvider $factoryProvider) {
    $factoryProvider->setAnnotationReader($this->annotationReader);
    $this->bind($factoryProvider->forInterface())->toProvider($factoryProvider);
  }

  protected function getProviderMethods(Module $module) {
    $reflectedClass = new \ReflectionClass($module);
    foreach ($reflectedClass->getMethods() as $reflectedMethod) {
      $provides = $this->annotationReader->getMethodAnnotation($reflectedMethod, Provides::CLASS);
      if ($provides) {
        $binding = $this->bind($provides->value)->toProvider(new ProviderMethod($module, $reflectedMethod->getName()));
        if ($this->annotationReader->getMethodAnnotation($reflectedMethod, Singleton::CLASS)) {
          $binding->in(Scopes::singleton());
        }
      }
    }
  }

  // TODO: this needs to handle chained linked bindings
  public function getBindingFor($interface) {
    if (isset($this->bindings[$interface])) return $this->bindings[$interface];
    return null;
  }

}
