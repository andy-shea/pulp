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
 * Designates a dependency that should be injected.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Inject {

  public static function shouldInject(Reflector $reflector): bool {
    $injectAttributes = $reflector->getAttributes(__CLASS__);
    if ($injectAttributes) {
      // validate attribute
      $injectAttributes[0]->newInstance();
      return true;
    }
    return false;
  }

}
