<?php
class sfPropelLuceneSearch
{
  protected $_queryString = "";
  protected $_models = array();
  
  public function __construct($sSearch = null)
  {
    $this->_queryString = $sSearch;
  }

  public static function create($sSearch = null)
  {
    return new self($sSearch);
  }
  
  public function in($models)
  {
    if (is_array($models))
    {
      $this->_models = $models;
    }
    else
    {
      $this->_models = func_get_args();
    }
    return $this;
  }

  public function find($limit = 10)
  {
    foreach ($this->_models as $model)
    {
      $hits = sfLuceneableToolkit::getLuceneIndex($model)->find($this->_queryString);
      $pks = array();
      foreach ($hits as $hit)
      {
        $pks[] = $hit->pk;
      }
      return PropelQuery::from($model)->limit($limit)->findPks($pks);
    }
  }
  
  public function paginate($page, $limit = 10)
  {
    foreach ($this->_models as $model)
    {
      $hits = sfLuceneableToolkit::getLuceneIndex($model)->find($this->_queryString);
      $pks = array();
      foreach ($hits as $hit)
      {
        $pks[] = $hit->pk;
      }
      return PropelQuery::from($model)->filterByPrimaryKeys($pks)->paginate($page, $limit);
    }
  }
}
