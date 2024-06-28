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
 * Designates the type of dependency to be returned from the automatically
 * injected provider.
 *
 * @author Andy Shea <aa.shea@gmail.com>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Provides {

  protected string $type;

  public static function extractProvidesType(Reflector $reflected): ?string {
    $providesAttributes = $reflected->getAttributes(__CLASS__);
    if ($providesAttributes) {
      $providesAttribute = $providesAttributes[0]->newInstance();
      return $providesAttribute->getType();
    }
    return null;
  }

  public function __construct(string $type) {
    $this->type = $type;
  }

  public function getType(): string {
    return $this->type;
  }

}
