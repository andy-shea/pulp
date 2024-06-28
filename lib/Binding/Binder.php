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
use Pulp\Meta\Attribute\Provides;
use Pulp\Meta\Attribute\Singleton;
use ReflectionClass;

/**
 * Collects binding configurations used to resolve dependency graphs.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class Binder {

  protected array $bindings = [];
  protected array $modules = [];

  public function install(Module $module): void {
    if (!isset($this->modules[spl_object_hash($module)])) {
      $module->setBinder($this);
      $module->configure();
      $this->getProviderMethods($module);
      $this->modules[spl_object_hash($module)] = true;
    }
  }

  public function bind(string $interface): Binding {
    $binding = new Binding($interface);
    $this->bindings[$interface] = $binding;
    return $binding;
  }

  public function installFactoryProvider(FactoryProvider $factoryProvider): void {
    $this->bind($factoryProvider->forInterface())->toProvider($factoryProvider);
  }

  protected function getProviderMethods(Module $module): void {
    $reflectedClass = new ReflectionClass($module);
    foreach ($reflectedClass->getMethods() as $reflectedMethod) {
      $providesType = Provides::extractProvidesType($reflectedMethod);
      if ($providesType) {
        $binding = $this->bind($providesType)
          ->toProvider(new ProviderMethod($module, $reflectedMethod->getName()));
        if (Singleton::isSingleton($reflectedMethod)) $binding->in(Scopes::singleton());
      }
    }
  }

  // TODO: this needs to handle chained linked bindings
  public function getBindingFor(string $interface): ?Binding {
    if (isset($this->bindings[$interface])) return $this->bindings[$interface];
    return null;
  }

}
