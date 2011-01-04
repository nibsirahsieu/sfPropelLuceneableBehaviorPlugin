
/**
 * delete lucene index for this class
 * @param zend lucene index $index
 * @return boolean
 */
public function deleteLuceneIndex()
{
  $index = sfLuceneableToolkit::getLuceneIndex(get_class($this));
  foreach ($index->find('<?php echo $data[0] ?>:'.$this-><?php echo $data[1] ?>) as $hit)
  {
    $index->delete($hit->id);
  }

  return true;
}

