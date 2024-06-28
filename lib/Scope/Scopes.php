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

/**
 * A support class to manage scope creation.  Use this to return an instance of
 * a scope to use when binding dependencies.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
class Scopes {

  protected static array $scopes = [];

  public static function __callStatic($name, $arguments): Scope {
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
