<?php
class sfPropelLuceneableBehaviorPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      sfLuceneableToolkit::registerZend();
    }
  }
}
