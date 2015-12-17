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

/**
 * A support class to manage scope creation.  Use this to return an instance of
 * a scope to use when binding dependencies.
 *
 * @author Andy Shea <andrew@octahedron.com.au>
 */
class Scopes {

  protected static $scopes = [];

  public static function __callStatic($name, $arguments) {
    if (!isset(self::$scopes[$name])) {
      switch ($name) {
        case 'instance': self::$scopes[$name] = new InstanceScope(); break;
        case 'singleton': self::$scopes[$name] = new SingletonScope(); break;
        default: throw new InvalidScopeException();
      }
    }
    return self::$scopes[$name];
  }

}
