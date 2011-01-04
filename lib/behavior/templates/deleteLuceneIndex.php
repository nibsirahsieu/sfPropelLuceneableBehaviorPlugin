
/**
 * delete lucene index for this class
 * @param zend lucene index $index
 * @return boolean
 */
public function deleteLuceneIndex()
{
  $index = sfLuceneableToolkit::getLuceneIndex(get_class($this));
  foreach ($index->find('pk:'.$this-><?php echo $method ?>) as $hit)
  {
    $index->delete($hit->id);
  }

  return true;
}
