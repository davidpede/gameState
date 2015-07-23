<?php
$xpdo = $modx->getPlaceholder("my.xpdo");
if ($xpdo !== "") {
  $xpdo->addPackage('league_pack','C:/xampp/htdocs/repo/revolution/core/components/league_pack/model/','');
}else{
  return 'Can not load package';
}

// Test your connection
//echo $o = ($xpdo->connect()) ? 'Connected' : 'Not Connected';

//$xpdo->setDebug(true);

/* set default properties */
$league = !empty($league) ? $league : '';
$rowTpl = !empty($rowTpl) ? $rowTpl : '';
$prefix = !empty($prefix) ? $prefix : '';
$result = '';

$c = $xpdo->newQuery($league);
$c->select('id,home');
$c->sortby('home','ASC');
$rows = $xpdo->getCollection($league,$c);

foreach ($rows as $row) {
  $array = $row->toArray('',false,true);
  $data[] = $array[home];
}

$rows = array_values(array_unique($data));

foreach ($rows as $row) {
  $ph[] = $modx->setPlaceholder($prefix.'teamName', $row);
  $result .= $modx->getChunk($rowTpl,$ph);
}

return $result;