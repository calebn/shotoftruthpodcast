<?php
require_once('scripts/ShotOfTruth.php');

//ini_set("xdebug.var_display_max_children", -1);
//ini_set("xdebug.var_display_max_data", -1);
//ini_set("xdebug.var_display_max_depth", -1);

$xml_file = 'shotoftruthpodcastrss.xml';

$res = file_get_contents($xml_file);

$shot = new \shotoftruth\ShotOfTruth($res);
$shot->regenSite();
