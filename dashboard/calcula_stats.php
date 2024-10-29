<?php
/**
 * @author William Sergio Minossi
 * @copyright 2012-30-07
 */
if (!defined('ABSPATH'))
  exit; // Exit if accessed directly
global $wpdb;
$table_name = $wpdb->prefix . "antibots_visitorslog";

/*
$query = 'SELECT DATE_FORMAT(date, "%m%d") as mydate
FROM   ' . $table_name . '
WHERE human = \'Bot\' and access = \'Denied\' and DATEDIFF(NOW(), date) <= 14 ORDER BY date DESC';
//$results = $wpdb->get_results($query, ARRAY_A);
*/

$query = 'SELECT DATE_FORMAT(date, "%m%d") as mydate
FROM   ' . $table_name . '
WHERE access <> \'OK\' and DATEDIFF(NOW(), date) <= 14 ORDER BY date DESC';
$results = $wpdb->get_results($query, ARRAY_A);

//var_dump($results);


if(!$results)
{

  $array30l = array();
  $array30 = array();
  return;

}

foreach ($results as $result) {
  $data[] = strval($result['mydate']);
}



$results8 = (array_count_values($data));
unset($results);
unset($data);

if(count($results8) < 1)
{
    $array30l = array();
    $array30 = array();
    return;
}
$x = 0;
$d = 15;
for ($i = $d; $i > 0; $i--) {
  $timestamp = time();
  $tm = 86400 * ($x); // 60 * 60 * 24 = 86400 = 1 day in seconds
  $tm = $timestamp - $tm;
  $the_day = date("d", $tm);
  $this_month = date('m', $tm);
  $array30d[$x] = $this_month . $the_day;
  if (isset($results8[$this_month . $the_day])) {
    $array30[$x] = $results8[$this_month . $the_day];
    //  $array30[$x] = $awork;
  } else
    $array30[$x] = 0;
  $x++;
}
$array30 = array_reverse($array30);
$array30d = array_reverse($array30d);

//var_dump($array30);


