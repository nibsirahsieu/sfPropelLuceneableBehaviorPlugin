<?php
class LuceneableBehavior extends Behavior
{
  protected  function getLuceneFieldMethod($method)
  {
    $availableMethods = array('keyword' => 'Keyword', 'unindexed' => 'UnIndexed', 'binary' => 'Binary', 'text' => 'Text', 'unstored' => 'UnStored');
    $method = strtolower($method);
    if (isset($availableMethods[$method])) return $availableMethods[$method];
    throw new Exception(sprintf('Unknown Lucene Field method %s', $method));
  }

  /**
  * Get the getter of the column of the behavior
  * @param string $col the column name
  * @return string The related getter, e.g. 'getSlug'
  */
  protected function getColumnGetter($col)
  {
    return 'get' . $this->getTable()->getColumn($col)->getPhpName();
  }

  public function postSave($builder)
  {
    return "if (\$affectedRows > 0) \$this->updateLuceneIndex();";
  }

  public function postDelete($builder)
  {
    return "\$this->deleteLuceneIndex();";
  }

  public function addStaticStoredIndex()
  {
    $data = array();
    $columns = $this->getParameters();
    if (empty($columns))
    {
      $table = $this->getTable();
      foreach ($table->getColumns() as $col) {
        $clo = strtolower($col->getName());
        $data[$clo] = $col->isPrimaryKey() ? 'Keyword' : 'Unstored';
      }
    }
    else
    {
      foreach ($columns as $col => $type)
      {
        $clo = strtolower($col);
        $data[$clo] = $this->getLuceneFieldMethod($type);
      }
    }
    return $this->renderTemplate('staticStoredIndex', array(
			'data'   => $data,
    ));
  }

  public function addGetIndexedColumns()
  {
    return $this->renderTemplate('getIndexedColumns');
  }
  
  public function staticAttributes($builder)
  {
    return $this->addStaticStoredIndex();
  }

  public function staticMethods($builder)
  {
    return $this->addGetIndexedColumns();
  }

  public function objectMethods($builder)
  {
    $script = '';
    $script .= $this->addUpdateLuceneIndex();
    $script .= $this->addDeleteLuceneIndex();

    return $script;
  }

  public function addUpdateLuceneIndex()
  {
    $data = array();
    $columns = $this->getParameters();
    if (empty($columns))
    {
      $table = $this->getTable();
      foreach ($table->getColumns() as $col) {
        //skip primary key
        if (!$col->isPrimaryKey())
        {
          $method = 'get'.$col->getPhpName().'()';
          $clo = strtolower($col->getName());
          $data[] = array('UnStored', $clo, $method);
        }
      }
    }
    else
    {
      foreach ($columns as $col => $type)
      {
        $method = $this->getColumnGetter($col).'()';
        $clo = strtolower($col);
        $fieldMethod = $this->getLuceneFieldMethod($type);
        $data[] = array($fieldMethod, $clo, $method);
      }
    }
    return $this->renderTemplate('updateLuceneIndex', array(
			'data'   => $data,
    ));
  }

  public function addDeleteLuceneIndex()
  {
    return $this->renderTemplate('deleteLuceneIndex');
  }
}