
/**
 * delete lucene index for this class
 * @param zend lucene index $index
 * @return boolean
 */
public function deleteLuceneIndex($index = null)
{
  if (null === $index) $index = sfLuceneableToolkit::getLuceneIndex(get_class($this));
  foreach ($index->find('pk:'.$this->getPrimaryKey()) as $hit)
  {
    $index->delete($hit->id);
  }

  return true;
}
