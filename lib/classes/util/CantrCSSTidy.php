<?php

/**
 * Class CantrCSSTidy extends csstidy just to be able to inject our custom CSSTidyOptimiseValidationExtension object to field "optimise".
 * This injected class performs strict validation of CSS selectors to make us safe from XSS.
 *
 * Injecting this value is very tricky, because field $optimise is overwritten directly in csstidy's [main] parse() method
 * (to fix some bug caused by php4). The only way to keep the correct value in this field is to unset $this->optimise and
 * use magic __get and __set methods.
 *
 * This way __set is called upon the first assignment to optimise. Field $optimise is not created
 * (so subsequent access to $optimise will also be redirected to  __set). Field $optimiseHolder method is created instead
 * whose value will be returned upon call to __get() for property $optimise.
 */
class CantrCSSTidy extends csstidy
{
  private $optimiseHolder;

  public function __construct()
  {
    parent::__construct();

    unset($this->optimise);
  }

  public function __set($property, $value)
  {
    if ($property == "optimise") { // field "optimise" can hold only our extension of default Optimiser
      $this->optimiseHolder = new CSSTidyOptimiseValidationExtension($this); // see lib/3rdparty/CSSTidy/readme.txt
    } else {
      $this->$property = $value;
    }
  }

  public function __get($property)
  {
    if ($property == "optimise") {
      return $this->optimiseHolder;
    }
    return null;
  }
}
