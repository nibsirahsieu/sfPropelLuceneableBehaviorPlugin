<?php
class sfPropelLuceneSearch
{
  protected $_queryString = "";
  protected $_model = '';
  
  public function __construct($model, $sSearch = null)
  {
    if (!($sSearch instanceof Zend_Search_Lucene_Search_Query))
    {
      $sSearch = Zend_Search_Lucene_Search_QueryParser::parse($sSearch);
    }
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
    return new sfLuceneModelResults($model, $hits, $this->_queryString);
  }

  public function paginate($page, $limit = 10)
  {
    $pager = new sfLucenePager($this->_model, $this->_queryString, $limit);
		$pager->setPage($page);
		$pager->init();
    return $pager;
  }
}