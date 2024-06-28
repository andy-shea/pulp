<?php

/*
 * This file is part of the Pulp package.
 *
 * (c) Andy Shea <aa.shea@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pulp\Meta\Attribute;

use Attribute;
use Reflector;

/**
 * Designates a singleton scope for the dependency.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Singleton {

  public static function isSingleton(Reflector $reflector): bool {
    $singletonAttributes = $reflector->getAttributes(__CLASS__);
    if ($singletonAttributes) {
      // validate attribute
      $singletonAttributes[0]->newInstance();
      return true;
    }
    return false;
  }

}
