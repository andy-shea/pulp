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
use ReflectionParameter;
use ReflectionProperty;
use ReflectionObject;

/**
 * Designates an annotated binding which can be used to distinguish between multiple bindings of
 * the same type.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Qualifier {

  public static function getQualifier(ReflectionParameter|ReflectionProperty $reflectedParameter): ?string {
    foreach ($reflectedParameter->getAttributes() as $attribute) {
      $reflectedAttribute = new ReflectionObject($attribute->newInstance());
      $qualifierAttributes = $reflectedAttribute->getAttributes(__CLASS__);
      if ($qualifierAttributes) {
        // validate attribute
        $qualifierAttributes[0]->newInstance();
        return $reflectedAttribute->getName();
      }
    }
    return null;
  }

}
