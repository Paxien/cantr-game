<?php

/**
 * Wrapper for array to allow chaining of methods using lambda expressions
 */
class Pipe
{
  /**
   * @var array
   */
  private $subject;

  public static function from(array $array)
  {
    $pipe = new self();
    $pipe->subject = $array;
    return $pipe;
  }

  private function __construct()
  {
  }

  public function filter($predicate)
  {
    $this->subject = array_filter($this->subject, $predicate);

    return $this;
  }

  public function filterKeys($predicate)
  {
    $newArray = array();
    foreach ($this->subject as $key => $value) {
      if ($predicate($key)) {
        $newArray[$key] = $value;
      }
    }
    $this->subject = $newArray;

    return $this;
  }

  public function map($func)
  {
    $this->subject = array_map($func, $this->subject);

    return $this;
  }

  public function mapKeys($func)
  {
    $newArray = array();
    foreach ($this->subject as $key => $value) {
      $newArray[$func($key)] = $value;
    }
    $this->subject = $newArray;

    return $this;
  }

  /**
   * Maps key-value pair into another key-value pair. In case of key conflicts in final array isn't unspecified which one will be overwritten.
   * @param $func Callable params($key, $value), returing a 1-element array (key and value) which will be added to mapped array.
   * @return $this
   */
  public function mapKV($func)
  {
    $newArray = array();
    foreach ($this->subject as $key => $value) {
      $pair = $func($key, $value);
      $retValue = reset($pair);
      $newArray[key($pair)] = $retValue;
    }
    $this->subject = $newArray;

    return $this;
  }

  /**
   * These are final functions in pipe and return values
   */

  /**
   * @return array array constrained in this pipe
   */
  public function toArray()
  {
    return $this->subject;
  }

  /**
   * Return the first element of the array or null if array is empty.
   * @return mixed|null
   */
  public function first()
  {
    if (empty($this->subject)) {
      return null;
    }
    return $this->subject[0];
  }

  /**
   * @return int number of elements in pipe
   */
  public function count()
  {
    return count($this->subject);
  }

  /**
   * Returns element got from reducing using a callback or null if array was empty
   * @param $func Callable two argument callable which takes first two pipe elements or accumulator and one pipe element as arguments
   * @return mixed|null accumulator
   */
  public function reduce($func)
  {
    if (count($this->subject) == 0) {
      return null;
    }

    $initial = array_shift($this->subject);
    return array_reduce($this->subject, $func, $initial);
  }

  /**
   * Implode values from the array into one string.
   * @param $separator string being separator
   * @return string string imploded from the array or an empty string if none
   */
  public function implode($separator)
  {
    if (count($this->subject) == 0) {
      return "";
    }
    return implode($separator, $this->subject);
  }

  /**
   * Reduce the array of values by returning the maximum of all of them.
   * It's assumed they are int or any other assignable for standard php max() function.
   * @return mixed the greatest value in the pipe
   */
  public function maximum()
  {
    return $this->reduce(function($a, $b) {
      return max($a, $b);
    });
  }

  /**
   * Reduce the array of values by returning the minimum of all of them.
   * It's assumed they are int or any other assignable for standard php min() function.
   * @return mixed the greatest value in the pipe
   */
  public function minimum()
  {
    return $this->reduce(function($a, $b) {
      return min($a, $b);
    });
  }

  public function partition($func)
  {
    $a = array();
    $b = array();
    foreach ($this->subject as $value) {
      if ($func($value)) {
        $a[] = $value;
      } else {
        $b[] = $value;
      }
    }
    return array($a, $b);
  }

  public function groupBy($func)
  {

    $result = [];
    foreach ($this->subject as $value) {
      $result[$func($value)][] = $value;
    }
    return $result;
  }
}
