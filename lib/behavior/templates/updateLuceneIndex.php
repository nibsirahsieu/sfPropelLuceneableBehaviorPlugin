
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

<?php if (null !== $deletedAtColumn): ?>
  /*
  soft_delete behavior exist in the schema.
  Note: 
  when a record is deleted by an user, it is not actually removed from database,
  it still exist there, but flagged as deleted (<?php echo $deletedAtColumn ?> != null).
  But it's index should be removed, so it'll be discarded from the searching results.
  */

  //record was deleted? got it, skip updating index
  if (null === $this-><?php echo $deletedAtColumnMethod ?>)
  {
<?php endif; ?>
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
<?php if (null !== $deletedAtColumn): ?>
  }
<?php endif; ?>
  return true;
}
