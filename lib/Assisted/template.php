
class <?php echo $this->factoryClass ?> implements <?php echo $this->factoryInterface ?> {

  private $injector;

  public function __construct(Pulp\Injector $injector) {
    $this->injector = $injector;
  }

  <?php foreach ($this->createFactoryMethods() as $factoryMethod) : ?>
  public function <?php echo $factoryMethod['name'] ?>(<?php echo implode(', ', $factoryMethod['args']) ?>) {
    return $this->injector->getInstance('<?php echo $factoryMethod['returns'] ?>'<?php if ($factoryMethod['args']) : ?>,
        [<?php echo implode(', ', array_map(fn($parameter) => "'$parameter' => $" . $parameter, array_keys($factoryMethod['args']))) ?>]<?php endif ?>);
  }
  <?php endforeach ?>

}
