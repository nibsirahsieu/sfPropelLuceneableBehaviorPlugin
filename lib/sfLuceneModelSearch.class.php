<?php
class sfLuceneModelSearch
{
  protected $_queryString = "";
  protected $_model = '';
  
  public function __construct($model, $sSearch = null)
  {
    $this->_queryString = $sSearch;
    $this->_model = $model;
  }

  public static function create($model, $sSearch = null)
  {
    return new self($model, $sSearch);
  }
  
  public function find($limit = 10)
  {
    $hits = sfLuceneableToolkit::getHits($this->_model, $this->_queryString);
    return PropelQuery::from($this->model)->findPks($hits);
  }

  public function paginate($page, $limit = 10)
  {
    $pager = new sfLucenePager($this->_model, $this->_queryString, $limit);
		$pager->setPage($page);
		$pager->init();
    return $pager;
  }
}