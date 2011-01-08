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
        $fieldMethods = array();
        $clo = $col->getPhpName();
        if (!$col->isPrimaryKey())
        {
          $fieldMethods[] = 'Unstored';
        }
        else
        {
          $fieldMethods[] = 'Keyword';
          $clo = 'pk';
        }
        $data[] = array($fieldMethods, $clo, 'get'.$col->getPhpName().'()');
      }
    }
    else
    {
      $keywordFound = false;
      foreach ($columns as $c => $type_boost)
      {
        $fieldMethods = array();
        $col = $this->getTable()->getColumn($c);
        $clo = $col->getPhpName();
        $type = explode(':', $type_boost);
        $fieldMethods[] = $this->getLuceneFieldMethod($type[0]);
        if (isset($type[1])) $fieldMethods[] = $type[1];
        if (strtolower($type[0]) == 'keyword')
        {
          $clo = 'pk';
          $keywordFound = true;
        }
        $data[] = array($fieldMethods, $clo, 'get'.$col->getPhpName().'()');
      }
      if (!$keywordFound)
      {
        $pks = $this->getTable()->getPrimaryKey();
        if (count($pks) > 0) $pks = $pks[0];
        $fieldMethods[] = 'Keyword';
        $data[] = array($fieldMethods, 'pk', 'get'.$pks->getPhpName().'()');
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