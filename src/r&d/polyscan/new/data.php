<?php
include('db_info.php');
function nextlast(){
$update = mysql_query("SELECT * FROM settings");
$update = mysql_fetch_array($update);
if($update['complete'] == '1'){
print 'Currently Updating';
} else {
$nstamp = $update['nexttime'] - time();
$lstamp = time() - $update['lasttime'];
print 'Next Update: ' . duration($nstamp) . '<br />';
print 'Last Update: ' . duration($lstamp) . '<br />';
}}

function duration($timestamp) {
    $years = floor($timestamp / (60 * 60 * 24 * 365));
    $timestamp %= 60 * 60 * 24 * 365;
    $weeks = floor($timestamp / (60 * 60 * 24 * 7));
    $timestamp %= 60 * 60 * 24 * 7;
    $days = floor($timestamp / (60 * 60 * 24));
    $timestamp %= 60 * 60 * 24;
    $hrs = floor($timestamp / (60 * 60));
    $timestamp %= 60 * 60;
    $mins = floor($timestamp / 60);
    $secs = $timestamp % 60;
    $str = "";
    if ($years == 1) {
        $str .= "{$years} year ";
    }elseif ($years > 1) {
        $str .= "{$years} yearss ";
    }
    if ($weeks == 1) {
        $str .= "{$weeks} week ";
    }elseif ($weeks > 1) {
        $str .= "{$weeks} weeks ";
    }   
    if ($days == 1) {
        $str .= "{$days} day ";
    }elseif ($days > 1) {
        $str .= "{$days} days ";
    }
    if ($hrs == 1) {
        $str .= "{$hrs} hour ";
    }elseif ($hrs > 1) {
        $str .= "{$hrs} hours ";
    }
    if ($mins == 1) {
        $str .= "{$mins} minute ";
    }elseif ($mins > 1) {
        $str .= "{$mins} minutes ";
    }
    if ($mins > 1 && $secs >= 1) {
        $str .= "{$secs} seconds ";
    }
    return $str;
}
$nn = count($qew);
for($i = 0; $i < $nn; $i++){
$front = mysql_num_rows(mysql_query("SELECT * FROM queue WHERE queue='$n' AND (complete='2' OR complete='0')"));
print 'Queue Size: ' . $front . '<br />';
}
nextlast();
?>