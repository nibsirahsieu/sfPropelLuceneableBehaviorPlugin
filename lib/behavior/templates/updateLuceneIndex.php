
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
<?php foreach ($data as $v): ?>
  $field = Zend_Search_Lucene_Field::<?php echo $v[0][0] ?>('<?php echo $v[1] ?>', $this-><?php echo $v[2] ?>, 'utf-8');
  <?php if (isset($v[0][1]) && null !== $boost = $v[0][1]): ?>
$field->boost = <?php echo $boost ?>;
  <?php endif; ?>
$doc->addField($field);
<?php endforeach; ?>

  $index->addDocument($doc);
  $index->commit();

  return true;
}
