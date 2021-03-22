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

use Pulp\Injector;
use Pulp\Scope\Scopes;
use Pulp\Scope\Scope;
use Pulp\Provider\Provider;
use Closure;

/**
 * A mapping of a dependency's interface or class to a strategy for realising that
 * dependency.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class Binding {

  protected string|int $interface;
  protected ?string $implementation = null;
  protected mixed $instance = null;
  protected Provider|Closure|null $provider = null;
  protected Scope $scope;

  public function __construct(string $interface) {
    $this->interface = $interface;
    $this->scope = Scopes::instance();
  }

  public function to(string $implementation): self {
    if ($implementation == $this->interface) throw new BindingException('Cannot bind an interface to itself');
    $this->implementation = $implementation;
    return $this;
  }

  public function toInstance(mixed $instance): self {
    $this->instance = $instance;
    return $this;
  }

  public function toProvider(Provider|Closure $provider): self {
    $this->provider = $provider;
    return $this;
  }

  public function in(Scope $scope): void {
    $this->scope = $scope;
  }

  protected function isBoundToInstance(): bool {
    return ($this->instance !== null);
  }

  protected function isBoundToImplementation(): bool {
    return ($this->implementation !== null);
  }

  protected function isBoundToProvider(): bool {
    return ($this->provider !== null);
  }

  public function getDependency(Injector $injector, array $assistedParams = null, bool $isOptional = false): mixed {
    return $this->scope->getDependency($this, $injector, $assistedParams, $isOptional);
  }

  // should only be called by a Scope implementation
  public function createDependency(Injector $injector, array $assistedParams = null, bool $isOptional = false): mixed {
    if ($this->isBoundToImplementation()) return $injector->getInstance($this->implementation, $assistedParams, $isOptional);
    if ($this->isBoundToProvider()) {
      // TODO: inject the parameters instead of the injector itself
      if (is_callable($this->provider)) return call_user_func($this->provider, $injector);

      $injector->injectMembers($this->provider);
      return $this->provider->get();
    }
    if ($this->isBoundToInstance()) return $this->instance;
    return $injector->createInstance($this->interface, $assistedParams, $isOptional);
  }

}
