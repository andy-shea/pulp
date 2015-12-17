<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Binding;

use Octahedron\Pulp\Module;
use Octahedron\Pulp\Provider\ProviderMethod;
use Octahedron\Pulp\Scope\Scopes;
use Octahedron\Pulp\Assisted\FactoryProvider;
use Doctrine\Common\Annotations\Reader;

/**
 * Collects binding configurations used to resolve dependency graphs.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
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
      $provides = $this->annotationReader->getMethodAnnotation($reflectedMethod, 'Octahedron\Pulp\Meta\Annotation\Provides');
      if ($provides) {
        $binding = $this->bind($provides->value)->toProvider(new ProviderMethod($module, $reflectedMethod->getName()));
        if ($this->annotationReader->getMethodAnnotation($reflectedMethod, 'Octahedron\Pulp\Meta\Annotation\Singleton')) {
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
