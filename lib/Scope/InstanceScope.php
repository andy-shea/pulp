<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Scope;

use Pulp\Binding\Binding;
use Pulp\Injector;

/**
 * Defines the scope for the dependency to be per instance where each dependency
 * of this type will be injected with a new instance. This is the default scope.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class InstanceScope implements Scope {

  public function getDependency(
    Binding $binding,
    Injector $injector,
    array $assistedParams = null,
    bool $isOptional = false
  ): mixed {
    return $binding->createDependency($injector, $assistedParams, $isOptional);
  }

}
