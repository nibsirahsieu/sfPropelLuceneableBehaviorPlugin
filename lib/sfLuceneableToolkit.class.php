<?php
class sfLuceneableToolkit
{
  public static $_index = array();
  static protected $zendLoaded = false;

  private static function prepareZendSearchLucene()
  {
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());

    $stopWords = sfConfig::get('app_sf_luceneable15_behavior_stopWords', false);
    $stopWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_StopWords(false === $stopWords ? array() : explode(',', $stopWords));
		Zend_Search_Lucene_Analysis_Analyzer::getDefault()->addFilter($stopWordsFilter);
    
    $shortWords = sfConfig::get('app_sf_luceneable15_behavior_shortWords', 3);
		$shortWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_ShortWords($shortWords);
		Zend_Search_Lucene_Analysis_Analyzer::getDefault()->addFilter($shortWordsFilter);

    Zend_Search_Lucene_Storage_Directory_Filesystem::setDefaultFilePermissions(0777);
	}
  
  static public function registerZend()
  {
    if (self::$zendLoaded)
    {
      return;
    }
    set_include_path(dirname(__FILE__).'/../vendor'.PATH_SEPARATOR.get_include_path());
    require_once dirname(__FILE__).'/../vendor/Zend/Loader/Autoloader.php';
    Zend_Loader_Autoloader::getInstance();
    self::$zendLoaded = true;
  }

  static public function getLuceneIndex($class)
  {
    if (isset(self::$_index[$class]))
    {
      return self::$_index[$class];
    }

    self::registerZend();
    
    if (file_exists($index = self::getLuceneIndexFile($class)))
    {
      $luceneIndex = Zend_Search_Lucene::open($index);
    }
    else
    {
      $luceneIndex = Zend_Search_Lucene::create($index);
      chmod($index, 0777);
    }
    self::$_index[$class] = $luceneIndex;
    return $luceneIndex;
  }

  static public function getLuceneIndexFile($class)
  {
    $data_dir = sfConfig::get('app_sf_luceneable15_behavior_data_dir', sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'lucene');
    return $data_dir.DIRECTORY_SEPARATOR.$class.'.index';
  }

  public static function optimizeIndex($class, $index = null)
  {
		if (null === $index) $index = self::getLuceneIndex($class);
		$index->optimize();
	}

  public static function removeIndex($class, $index = null)
  {
    if (null === $index) $index = self::getLuceneIndex($class);
		if (file_exists($index))
    {
			sfToolkit::clearDirectory($index);
			rmdir($index);
		}
	}

  public static function createIndex($class, $index = null)
  {
    if (null === $index) $index = self::getLuceneIndex($class);
    $objects = PropelQuery::from($class)->find();
    foreach ($objects as $object)
    {
      $object->updateLuceneIndex($index);
    }
  }
}