<?php
class sfLuceneModelResults implements Iterator, Countable, ArrayAccess
{
  protected $model;
  protected $results;
  protected $pointer = 0;
  protected $indexedColums = array();
  protected $query = null;

  public function __construct($model, $results, $query)
  {
    $this->model = $model;
    $this->results = $results;
    $this->query = $query;
    $this->indexedColumns = call_user_func(array($model.'Peer', 'getIndexedColumns'));
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
    $tmp = array();
    foreach ($this->indexedColumns as $col => $type)
    {
      if (strtolower($type) == 'text')
      {
        $tmp[$col] = $this->query->htmlFragmentHighlightMatches($result->$col);
      }
      else
      {
        $tmp[$col] = $result->$col;
      }
    }
    $object = new $this->model;
    $object->fromArray($tmp, BasePeer::TYPE_FIELDNAME);
    $object->setNew(false);
    $object->resetModified();
    return $object;
  }
}
