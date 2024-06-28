<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Meta;

/**
 * Collects meta-information for a single dependency of an injectable method.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class InjectionParameter {

  protected string $name;
  protected string $interface;
  protected ?string $alias = null;
  protected bool $isAssisted = false;
  protected bool $isOptional = false;
  protected mixed $default;
  protected ?string $provides = null;

  public function __construct(string $name) {
    $this->name = $name;
  }

  public function name(): string {
    return $this->name;
  }

  public function setIsAssisted(bool $isAssisted): void {
    $this->isAssisted = $isAssisted;
  }

  public function isAssisted(): bool {
    return $this->isAssisted;
  }

  public function setInterface(string $interface): void {
    $this->interface = $interface;
  }

  public function setAlias(string $alias): void {
    $this->alias = $alias;
  }

  public function isOptional(): bool {
    return $this->isOptional;
  }

  public function setDefaultValue(mixed $default): void {
    $this->default = $default;
    $this->isOptional = true;
  }

  public function defaultValue(): mixed {
    return $this->default;
  }

  public function setProvides(string $provides): void {
    $this->provides = $provides;
  }

  public function isProvider(): bool {
    return !!$this->provides;
  }

  public function provides(): string {
    return $this->provides;
  }

  public function type(): string {
    if ($this->alias !== null) return $this->alias;
    if ($this->interface !== null) return $this->interface;
    return $this->name;
  }

}
