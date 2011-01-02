
/**
 * update lucene index for this class
 * @param zend lucene index $index
 * @return boolean
 */
public function updateLuceneIndex()
{
  $index = sfLuceneableToolkit::getLuceneIndex(get_class($this));

  // remove existing entries
  $this->deleteLuceneIndex($index);

  $doc = new Zend_Search_Lucene_Document();
  $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', $this->getPrimaryKey()));
<?php foreach ($data as $v): ?>
  $doc->addField(Zend_Search_Lucene_Field::<?php echo $v[0] ?>('<?php echo $v[1] ?>', $this-><?php echo $v[2] ?>, 'utf-8'));
<?php endforeach; ?>

  $index->addDocument($doc);
  $index->commit();

  return true;
}
