<?php

/**
 * symfony task for creating lucene indexes
 *
 * @package    sfLuceneable15BehaviorPlugin
 * @subpackage lib.task
 * @author     nibsirahsieu
 */
class sfPropelLuceneCreateIndexTask extends sfPropelBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('generator-class', null, sfCommandOption::PARAMETER_REQUIRED, 'The generator class', 'sfPropelLuceneGenerator'),
    ));

    $this->namespace = 'propel';
    $this->name = 'lucene-create-index';
    $this->briefDescription = 'Creates lucene index for the current model';

    $this->detailedDescription = <<<EOF
The [propel:lucene-create-index|INFO] task creates lucene index from the schema:

  [./symfony lucene-create-index|INFO]

The task read the schema information in [config/*schema.xml|COMMENT] and/or
[config/*schema.yml|COMMENT] from the project and all installed plugins.

The task use the [propel|COMMENT] connection as defined in [config/databases.yml|COMMENT].
You can use another connection by using the [--connection|COMMENT] option:

  [./symfony propel:lucene-create-index --connection="name"|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('propel', 'generating lucene indexes...');

    $generatorManager = new sfGeneratorManager($this->configuration);
    $luceneableModels = $generatorManager->generate($options['generator-class'], array(
      'connection'     => $options['connection']
    ));

    $databaseManager = new sfDatabaseManager($this->configuration);
    
    foreach ($luceneableModels as $luceneableModel)
    {
      $this->logSection('propel', sprintf('Recreating lucene index for %s model', $luceneableModel));
      $index = sfLuceneableToolkit::getLuceneIndex($luceneableModel);
      sfLuceneableToolkit::removeIndex($luceneableModel);
      sfLuceneableToolkit::createIndex($luceneableModel);
      $this->logSection('propel', sprintf('Optimizing lucene index for %s model', $luceneableModel));
      sfLuceneableToolkit::optimizeIndex($luceneableModel);
    }
    
    $this->logSection('propel', 'done...');
  }

}
