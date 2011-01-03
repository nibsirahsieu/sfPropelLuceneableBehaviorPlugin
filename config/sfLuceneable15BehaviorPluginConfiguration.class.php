<?php
class sfLuceneable15BehaviorPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      sfLuceneableToolkit::registerZend();
    }
  }
}