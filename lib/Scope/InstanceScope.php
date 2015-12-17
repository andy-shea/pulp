<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Octahedron Pty Ltd <andrew@octahedron.com.au>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octahedron\Pulp\Scope;

use Octahedron\Pulp\Binding\Binding;
use Octahedron\Pulp\Injector;

/**
 * Defines the scope for the dependency to be per instance where each dependency
 * of this type will be injected with a new instance. This is the default scope.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class InstanceScope implements Scope {

  public function getDependency(Binding $binding, Injector $injector, $assistedParams = null, $isOptional = false) {
    return $binding->createDependency($injector, $assistedParams, $isOptional);
  }

}
