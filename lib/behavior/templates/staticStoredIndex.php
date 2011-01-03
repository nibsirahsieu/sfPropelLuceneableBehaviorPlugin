
/**
* holds an array of indexed columns
*
*/
private static $_indexedColumns = array(
<?php foreach ($data as $column => $method): ?>
  '<?php echo $column ?>' => '<?php echo $method ?>',
<?php endforeach; ?>
);
