<?php

class sfLucenePager
{
  protected $model = null;
  protected $results = array();
  protected $search = null;
  protected $page = 1, $perPage = 5, $nbResults = 0, $lastPage = 0;
  protected $maxRecordLimit = false;

  public function __construct($model, $search, $perPage = 5)
  {
    $this->model = $model;
    $this->search = $search;
    $this->perPage = $perPage;
  }

  public function init()
  {
    $hasMaxRecordLimit = ($this->getMaxRecordLimit() !== false);
    $maxRecordLimit = $this->getMaxRecordLimit();

    $hits = sfLuceneableToolkit::getHits($this->model, $this->search);
    $count = count($hits);
    $this->setNbResults($hasMaxRecordLimit ? min($count, $maxRecordLimit) : $count);
    if (($this->getPage() == 0 || $this->getMaxPerPage() == 0)) {
      $this->setLastPage(0);
    } else {
      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
    }
    $this->results = $hits;
  }
  
  public function getLinks($nb_links = 5)
  {
    $links = array();
    $tmp   = $this->getPage() - floor($nb_links / 2);
    $check = $this->getLastPage() - $nb_links + 1;
    $limit = ($check > 0) ? $check : 1;
    $begin = ($tmp > 0) ? (($tmp > $limit) ? $limit : $tmp) : 1;

    $i = $begin;
    while (($i < $begin + $nb_links) && ($i <= $this->getLastPage()))
    {
      $links[] = $i++;
    }

    return $links;
  }

  public function haveToPaginate()
  {
    return (($this->getPage() != 0) && ($this->getNbResults() > $this->getMaxPerPage()));
  }

  public function getMaxPerPage()
  {
    return $this->perPage;
  }

  public function setMaxPerPage($per)
  {
    $this->perPage = $per;
  }

  public function setPage($page)
  {
    $this->page = $page;
  }

  public function getNbResults()
  {
    return $this->nbResults;
  }

  protected function setNbResults($nb)
  {
    $this->nbResults = $nb;
  }

  public function getMaxRecordLimit()
  {
    return $this->maxRecordLimit;
  }

  public function setMaxRecordLimit($limit)
  {
    $this->maxRecordLimit = $limit;
  }

  public function getPage()
  {
    return $this->page;
  }

  public function getResults()
  {
    $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
    $limit = $this->getMaxPerPage();
    if ($limit == 0)
    {
      $results = $this->results;
    }
    $results = array_slice($this->results, $offset, $limit);
    if ($results) return PropelQuery::from($this->model)->findPks($results);
    return array();
  }

  public function getFirstPage()
  {
    return 1;
  }

  public function getLastPage()
  {
    return $this->lastPage;
  }

  protected function setLastPage($page)
  {
    $this->lastPage = $page;
    if ($this->getPage() > $page) {
      $this->setPage($page);
    }
  }

  public function getNextPage()
  {
    return min($this->getPage() + 1, $this->getLastPage());
  }

  public function getPreviousPage()
  {
    return max($this->getPage() - 1, $this->getFirstPage());
  }

  public function isFirstPage()
  {
    return $this->getPage() == $this->getFirstPage();
  }

  public function isLastPage()
  {
    return $this->getPage() == $this->getLastPage();
  }
}
