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
 * Defines the scope for the dependency to be a singleton where the same
 * instance will be injected each time this dependency is required.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class SingletonScope implements Scope {

  protected $dependencies = array();

  public function getDependency(Binding $binding, Injector $injector, $assistedParams = null, $isOptional = false) {
    $uniqueHash = $this->getUniqueHash($binding, $assistedParams);
    if (!isset($this->dependencies[$uniqueHash])) {
      $this->dependencies[$uniqueHash] = $binding->createDependency($injector, $assistedParams, $isOptional);
    }
    return $this->dependencies[$uniqueHash];
  }

  protected function getUniqueHash($binding, $assistedParams) {
    $hash = [spl_object_hash($binding)];
    if ($assistedParams) $this->appendParamsHash($hash, $assistedParams);
    return implode(':::', $hash);
  }

  protected function appendParamsHash(&$hash, $params) {
    foreach ($params as $param) {
      if (is_object($param)) $hash[] = spl_object_hash($param);
      else if (is_array($param)) $this->appendParamsHash($hash, $param);
      else $hash[] = $param;
    }
  }

}
