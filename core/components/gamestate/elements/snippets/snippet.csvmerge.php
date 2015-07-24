<?php
$rowTpl = !empty($rowTpl) ? $rowTpl : '';
$season = !empty($season) ? $season : '';
$league = !empty($league) ? $league : '';
$div = !empty($div) ? $div : '';
$round = !empty($round) ? $round : '';

$output = '';

$fileA = fopen("C:\\xampp\\htdocs\\repo\\gameState\\data\\league\\".$league."\\L".$div."\\1415_rd".$round.".csv", 'r');
$fileB = fopen("C:\\xampp\\htdocs\\repo\\gameState\\data\\league\\".$league."\\L".$div."\\1415_rd".$round."gt.csv", 'r');

while (($tmp1 = fgetcsv($fileA)) !== FALSE) {
  $matches[] = $tmp1;
}

//echo '<pre>';
//print_r ($matches);
//echo '</pre>';

while (($tmp2 = fgetcsv($fileB)) !== FALSE) {
  $goals[] = $tmp2;
}

//echo '<pre>';
//print_r ($goals);
//echo '</pre>';

fclose($fileA);
fclose($fileB);

foreach ($matches as $key => $match) {
  foreach ($goals as $goal) {
    if ($match[6] === $goal[5] && $match[1] === $goal[1]) { //
      $fthgt = $goal[3];
      $ftagt = $goal[2];
      $date = date("d/m/y", strtotime($goal[0]));
    }
  }
  $array[$key][season] = $season;
  $array[$key][date] = $date;
  $array[$key][home] = $match[6];
  $array[$key][away] = $match[1];
  $array[$key][fthg] = $match[5];
  $array[$key][fthgt] = str_replace(';','',$fthgt);
  $array[$key][ftag] = $match[9];
  $array[$key][ftagt] = str_replace(';','',$ftagt);
  $array[$key][hthg] = $match[10];
  $array[$key][htag] = $match[11];
}

unset($array[0]);

//echo '<pre>';
//print_r ($array);
//echo '</pre>';

foreach ($array as $line) {
  $results .= $modx->getChunk($rowTpl,$line);
}

$output = $results;

return $output;