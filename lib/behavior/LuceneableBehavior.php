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
        $clo = $col->getPhpName();
        if ($col->isPrimaryKey())
        {
          $clo = 'pk';
          $data[$clo] = 'Keyword';
        }
        else 
        {
          $data[$clo] = 'Unstored';
        }
      }
    }
    else
    {
      $keywordFound = false;
      foreach ($columns as $c => $type)
      {
        $col = $this->getTable()->getColumn($c);
        $clo = $col->getPhpName();
        $fieldMethod = $this->getLuceneFieldMethod($type);
        if (strtolower($type) == 'keyword')
        {
          $keywordFound = true;
        }
        $data[$clo] = $this->getLuceneFieldMethod($type);
      }
      if (!$keywordFound)
      {
        $pks = $this->getTable()->getPrimaryKey();
        if (count($pks) > 0) $pks = $pks[0];
        $data[$pks->getPhpName()] = $this->getLuceneFieldMethod($type);
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
        $clo = strtolower($col->getName());
        $method = 'get'.$col->getPhpName().'()';
        if (!$col->isPrimaryKey())
        {
          $fieldMethod = 'Unstored';
        }
        else
        {
          $clo = 'pk';
        }
        $data[] = array($fieldMethod, $clo, $method);
      }
    }
    else
    {
      $keywordFound = false;
      foreach ($columns as $c => $type)
      {
        $col = $this->getTable()->getColumn($c);
        $clo = strtolower($col->getName());
        $method = 'get'.$col->getPhpName().'()';
        $fieldMethod = $this->getLuceneFieldMethod($type);
        if (strtolower($type) == 'keyword')
        {
          $clo = 'pk';
          $keywordFound = true;
        }
        $data[] = array($fieldMethod, $clo, $method);
      }
      if (!$keywordFound)
      {
        $pks = $this->getTable()->getPrimaryKey();
        if (count($pks) > 0) $pks = $pks[0];
        $data[] = array('keyword', 'pk', 'get'.$pks->getPhpName().'()');
      }
    }
    return $this->renderTemplate('updateLuceneIndex', array(
			'data'   => $data,
    ));
  }

  public function addDeleteLuceneIndex()
  {
    $method = null;
    $columns = $this->getParameters();
    if (empty($columns))
    {
      $table = $this->getTable();
      foreach ($table->getColumns() as $col)
      {
        if ($col->isPrimaryKey())
        {
          $method =  'get'.$col->getPhpName().'()';
          break;
        }
      }
    }
    else
    {
      foreach ($columns as $c => $type)
      {
        $col = $this->getTable()->getColumn($c);
        if (strtolower($type) == 'keyword')
        {
          $method = 'get'.$col->getPhpName().'()';
          break;
        }
      }
      if ($method == null)
      {
        $pks = $this->getTable()->getPrimaryKey();
        if (count($pks) > 0) $pks = $pks[0];
        $method = 'get'.$pks->getPhpName().'()';
      }
    }
    return $this->renderTemplate('deleteLuceneIndex', array('method' => $method));
  }
}