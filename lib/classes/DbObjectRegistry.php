<?php

/**
 * Should be changed to Weak References when it'll be available in PHP
 * @author Aleksander Chrabaszcz
 */
class DbObjectRegistry
{
  private $objects = array();

  public function put($key, $object)
  {
    $this->objects[$key] = $object;
  }

  public function get($key)
  {
    if ($this->contains($key)) {
      return $this->objects[$key];
    }
    return null;
  }

  public function contains($key)
  {
    return isset($this->objects[$key]);
  }

  /**
   * @return true if element was present in registry
   */
  public function remove($key)
  {
    $ret = isset($this->objects[$key]);
    unset($this->objects[$key]);
    return $ret;
  }

  public function clear()
  {
    $this->objects = array();
  }
  
}
