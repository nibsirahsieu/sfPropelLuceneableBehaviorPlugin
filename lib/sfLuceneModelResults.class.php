<?php
class sfLuceneModelResults implements Iterator, Countable, ArrayAccess
{
  protected $model;
  protected $results;
  protected $pointer = 0;
  
  public function __construct($model, $pks)
  {
    $this->model = $model;
    $this->results = $this->collectResults($model, $pks);
  }

  protected function collectResults($model, $pks)
  {
    $indexedColumns = call_user_func(array($model.'Peer', 'getIndexedColumns'));
    $columns = array_keys($indexedColumns);
    return PropelQuery::from($model)->select($columns)->findPks($pks);
  }
  
  public function current()
  {
    return $this->hydrate($this->results[$this->pointer]);
  }

  public function key()
  {
    return $this->pointer;
  }

  public function next()
  {
    $this->pointer++;
  }

  public function rewind()
  {
    $this->pointer = 0;
  }

  public function valid()
  {
    return isset($this->results[$this->pointer]);
  }

  public function count()
  {
    return count($this->results);
  }

  public function offsetExists($offset)
  {
    return isset($this->results[$offset]);
  }

  public function offsetGet($offset)
  {
    return $this->results[$offset];
  }

  public function offsetSet($offset, $set)
  {
    $this->results[$offset] = $set;
  }

  public function offsetUnset($offset)
  {
    unset($this->results[$offset]);
  }

  protected function hydrate($result)
  {
    $object = new $this->model;
    $object->fromArray($result, BasePeer::TYPE_PHPNAME);
    $object->setNew(false);
    $object->resetModified();
    return $object;
  }
}