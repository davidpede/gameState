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
$league = !empty($league) ? $league : false;
$htscore = !empty($htscore) ? $htscore : 'na';
$season = !empty($season) ? $season : 'na';
$goaltime = !empty($goaltime) ? explode('-', $goaltime) : 'na';
$gtFilterVar01 = !empty($goaltime) ? $goaltime[1] : false;
$gtFilterVar02 = !empty($goaltime) ? $goaltime[2] : false;
if ($htscore === '1-0' || $htscore === '0-1' || $htscore === 'na') {
  $eqlGoaltime = !empty($eqlGoaltime) ? $eqlGoaltime : 'na';
}else{
  $eqlGoaltime = 'na';
}
$homeTeam = !empty($homeTeam) ? $homeTeam : 'na';
$awayTeam = !empty($awayTeam) ? $awayTeam : 'na';
/* set caching options */
$dopt = array(
  xPDO::OPT_CACHE_KEY => 'lastUpdated',
  xPDO::OPT_CACHE_EXPIRES => 0,
);
$copt = array(
  xPDO::OPT_CACHE_KEY => 'leagueQuery',
  xPDO::OPT_CACHE_EXPIRES => 0,
);

$output = '';

if ($league) {

//Last updated
$updated = $modx->cacheManager->get($league, $dopt);
if (is_null($updated)) {
  $d = $xpdo->newQuery($league);
  $d->select('id,date');
  $d->sortby('id','DESC');
  $d->limit(1);
  $d = $xpdo->getObject($league,$d);
  $date = !empty($d) ? $d->get('date') : '';

  $modx->cacheManager->set($league, $date, 0, $dopt);
  $updated = $modx->cacheManager->get($league, $dopt);
}
//Database Query
$matches = $modx->cacheManager->get($league."_".$htscore."_".$season."_".$homeTeam."_".$awayTeam, $copt);
if (is_null($matches)) {
  $c = $xpdo->newQuery($league);
  if ($htscore != 'na') {
    $hts = explode('-', $htscore);
    $c->where(array(
      'hthg' => $hts[0],
      'htag' => $hts[1],
    ));
  }
  if ($season != 'na') {
    $c->where(array(
      'season' => $season,
    ));
  }
  if ($homeTeam != 'na') {
    $c->where(array(
      'home' => $homeTeam,
    ));
  }
  if ($awayTeam != 'na') {
    $c->where(array(
      'away' => $awayTeam,
    ));
  }
  $fixtures = $xpdo->getCollection($league,$c);

  foreach ($fixtures as $fixture) {
    $array = $fixture->toArray();

    //Extract Goal Times
    preg_match_all('/[\d]+/ui',$array[fthgt],$fthgt);
    $array[fthgt] = !empty($fthgt[0]) ? $fthgt[0] : false; //All home goal times
    $array[ffthgt] = !empty($array[fthgt]) ? $array[fthgt][0] : false; //First home goal time
    $array[sfthgt] = !empty($array[fthgt]) ? $array[fthgt][1] : false; //Second home goal time
    $array[lfthgt] = !empty($array[fthgt]) ? end(array_values($array[fthgt])) : false; //Last home goal time
    preg_match_all('/[\d]+/ui',$array[ftagt],$ftagt);
    $array[ftagt] = !empty($ftagt[0]) ? $ftagt[0] : false; //All away goal times
    $array[fftagt] = !empty($array[ftagt]) ? $array[ftagt][0] : false; //First away goal time
    $array[sftagt] = !empty($array[ftagt]) ? $array[ftagt][1] : false; //Second away goal time
    $array[lftagt] = !empty($array[ftagt]) ? end(array_values($array[ftagt])) : false; //Last away goal time
    //Second Half Goals
    $shhg = $array[fthg] - $array[hthg];
    $shag = $array[ftag] - $array[htag];
    $shg = $shhg + $shag;
    //Second Half Results
    if ($shhg > $shag) {
      $array[shr] = 'h';
    }elseif ($shhg == $shag) {
      $array[shr] = 'd';
    }elseif ($shhg < $shag) {
      $array[shr] = 'a';
    }
    //LATE GOAL
    if ($array[lfthgt] > '79' || $array[lftagt] > '79') {
      $array[lg] = 'y';
    }else {
      $array[lg] = 'n';
    }
    //SECOND HALF HOME Over 0.5 Goals
    if ($shhg > '0.5') {
      $array[shho05] = 'y';
    }else {
      $array[shho05] = 'n';
    }
    //SECOND HALF AWAY Over 0.5 Goals
    if ($shag > '0.5') {
      $array[shao05] = 'y';
    }else {
      $array[shao05] = 'n';
    }
    //BTTS
    if ($array[fthg] > 0 && $array[ftag] > 0) {
      $array[btts] = 'y';
    }else{
      $array[btts] = 'n';
    }
    //BTTS First Half
    if ($array[hthg] > 0 && $array[htag] > 0) {
      $array[fhbtts] = 'y';
    }else{
      $array[fhbtts] = 'n';
    }
    //BTTS Second Half
    if ($shhg > 0 && $shag > 0) {
      $array[shbtts] = 'y';
    }else{
      $array[shbtts] = 'n';
    }
    //SECOND HALF GoalLine 1
    if ($shhg + $shag >= '1') {
      $array[sho15] = 'y';
    }else {
      $array[sho15] = 'n';
    }
    //First Half Over 1.5 Goals
    if ($array[hthg] + $array[htag] > '1.5') {
      $array[fho15] = 'y';
    }else {
      $array[fho15] = 'n';
    }
    //Over 2.5 Goals
    if ($array[fthg] + $array[ftag] > '2.5') {
      $array[o25] = 'y';
    }else {
      $array[o25] = 'n';
    }
    //Over 3.5 Goals
    if ($array[fthg] + $array[ftag] > '3.5') {
      $array[o35] = 'y';
    }else {
      $array[o35] = 'n';
    }
    //Correct Score
    $array[cs] = $array[fthg] . "-" . $array[ftag];
    //Clean sheet Home
    if ($array[ftag] === 0) {
      $array[csh] = 'y';
    }else{
      $array[csh] = 'n';
    }
    //Clean sheet Away
    if ($array[fthg] === 0) {
      $array[csa] = 'y';
    }else{
    $array[csa] = 'n';
    }
    //Final Result
    if ($array[fthg] > $array[ftag]) {
      $array[ftr] = 'h';
    }elseif ($array[fthg] === $array[ftag]) {
      $array[ftr] = 'd';
    }elseif ($array[fthg] < $array[ftag]) {
      $array[ftr] = 'a';
    }
    //Final Array
    $dataset[] = $array;
  }
  $modx->cacheManager->set($league."_".$htscore."_".$season."_".$homeTeam."_".$awayTeam, $dataset, 0, $copt);
  $matches = $modx->cacheManager->get($league."_".$htscore."_".$season."_".$homeTeam."_".$awayTeam, $copt);
}

if ($goaltime === 'na' && $eqlGoaltime === 'na') {
  foreach ($matches as $array) {
    //Final Result
    $countftr[$array[ftr]]++;
    //Second Half Results
    $countshr[$array[shr]]++;
    //LATE GOAL
    $countlg[$array[lg]]++;
    //SECOND HALF HOME Over 0.5 Goals
    $countshho05[$array[shho05]]++;
    //SECOND HALF AWAY Over 0.5 Goals
    $countshao05[$array[shao05]]++;
    //BTTS
    $countbtts[$array[btts]]++;
    //BTTS First Half
    $countfhbtts[$array[fhbtts]]++;
    //BTTS Second Half
    $countshbtts[$array[shbtts]]++;
    //SECOND HALF GoalLine 1
    $countsho15[$array[sho15]]++;
    //First Half Over 1.5 Goals
    $countfho15[$array[fho15]]++;
    //Over 2.5 Goals
    $counto25[$array[o25]]++;
    //Over 3.5 Goals
    $counto35[$array[o35]]++;
    //Correct Score
    $countcs[$array[cs]]++;
    //Clean sheet Home
    $countcsh[$array[csh]]++;
    //Clean sheet Away
    $countcsa[$array[csa]]++;
    $total++;
  }
}else{
  foreach ($matches as $array) {
    if ($goaltime !== 'na') {
      $gtFilter = empty($array[$goaltime[0]]) ? false : $array[$goaltime[0]][0];
      $gtFilterOpVar01 = empty($array[ffthgt]) ? '100' : $array[ffthgt];
      $gtFilterOpVar02 = empty($array[fftagt]) ? '100' : $array[fftagt];
      $gtFilterOp = $goaltime[0] == 'fthgt' ? $gtFilterOpVar02 : $gtFilterOpVar01;
    }
    if ($eqlGoaltime !== 'na') {
      $gtEqlFilter = empty($array[$eqlGoaltime]) ? false : $array[$eqlGoaltime][0];
      $gtFilterOpVar01 = empty($array[sfthgt]) ? '100' : $array[sfthgt];
      $gtFilterOpVar02 = empty($array[sftagt]) ? '100' : $array[sftagt];
      $gtEqlFilterOp = $eqlGoaltime == 'fthgt' ? $gtFilterOpVar02 : $gtFilterOpVar01;
    }
    if ($goaltime === 'na' || $gtFilter >= $gtFilterVar01 && $gtFilter <= $gtFilterVar02 && $gtFilter < $gtFilterOp) {
      if ($eqlGoaltime === 'na' || !empty($gtEqlFilter) && $gtEqlFilter < $gtEqlFilterOp) {
      //Final Result
      $countftr[$array[ftr]]++;
      //Second Half Results
      $countshr[$array[shr]]++;
      //LATE GOAL
      $countlg[$array[lg]]++;
      //SECOND HALF HOME Over 0.5 Goals
      $countshho05[$array[shho05]]++;
      //SECOND HALF AWAY Over 0.5 Goals
      $countshao05[$array[shao05]]++;
      //BTTS
      $countbtts[$array[btts]]++;
      //BTTS First Half
      $countfhbtts[$array[fhbtts]]++;
      //BTTS Second Half
      $countshbtts[$array[shbtts]]++;
      //SECOND HALF GoalLine 1
      $countsho15[$array[sho15]]++;
      //First Half Over 1.5 Goals
      $countfho15[$array[fho15]]++;
      //Over 2.5 Goals
      $counto25[$array[o25]]++;
      //Over 3.5 Goals
      $counto35[$array[o35]]++;
      //Correct Score
      $countcs[$array[cs]]++;
      //Clean sheet Home
      $countcsh[$array[csh]]++;
      //Clean sheet Away
      $countcsa[$array[csa]]++;
      $total++;
      }
    }
  }
}

//Set Total Placeholders
$results[ftr] = $countftr;
$results[shr] = $countshr;
$results[btts] = $countbtts;
$results[fhbtts] = $countfhbtts;
$results[shbtts] = $countshbtts;
$results[sho15] = $countsho15;
$results[shho05] = $countshho05;
$results[shao05] = $countshao05;
$results[fho15] = $countfho15;
$results[o25] = $counto25;
$results[o35] = $counto35;
$results[csh] = $countcsh;
$results[csa] = $countcsa;
$results[lg] = $countlg;

foreach ($results as $prefix => $array) {
  foreach ($array as $key => $value) {
    $ph[$prefix.'.'.$key] = $value;
    $ph[$prefix.'.odd.'.$key] = round('100' / round($value / $total * '100'),2);
    $ph[$prefix.'.per.'.$key] = round($value / $total * '100', 1, PHP_ROUND_HALF_DOWN);
  }
}
//Set Manual Placeholders
$ph[total] = $total;
$ph[updated] = $updated;

//Set Correct Score Placeholder
ksort($countcs);
foreach ($countcs as $key => $value) {
  $per = round($value / $total * '100', 1, PHP_ROUND_HALF_DOWN);
  $score .= "[\"{$key}\",{$value},\"{$per}%\"],";
}
$scores = $modx->setPlaceholder('score',$score);

//echo '<pre>';
//print_r($results);
//echo '</pre>';

//echo '<pre>';
//print_r($ph);
//echo '</pre>';

$output = $modx->setPlaceholders($ph);

}

return $output;