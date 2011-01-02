<?php
class sfPropelLuceneGenerator extends sfGenerator
{
  protected
    $dbMap = null;

  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfPropelLucene');
  }

  public function generate($params = array())
  {
    $this->params = $params;

    if (!isset($this->params['connection']))
    {
      throw new sfParseException('You must specify a "connection" parameter.');
    }

    $this->dbMap = Propel::getDatabaseMap($this->params['connection']);

    $this->loadBuilders();

    $luceneableModels = array();
    foreach ($this->dbMap->getTables() as $tableName => $table)
    {
      $behaviors = $table->getBehaviors();
      if (isset($behaviors['luceneable']))
      {
        $luceneableModels[] = $table->getClassname();
      }      
    }
    return $luceneableModels;
  }

  protected function loadBuilders()
  {
    $this->dbMap = Propel::getDatabaseMap($this->params['connection']);
    $classes = sfFinder::type('file')->name('*TableMap.php')->in($this->generatorManager->getConfiguration()->getModelDirs());
    foreach ($classes as $class)
    {
      $omClass = basename($class, 'TableMap.php');
      if (class_exists($omClass) && is_subclass_of($omClass, 'BaseObject'))
      {
        $tableMapClass = basename($class, '.php');
        $this->dbMap->addTableFromMapClass($tableMapClass);
      }
    }
  }
}