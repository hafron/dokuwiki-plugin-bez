<?php
include_once DOKU_PLUGIN."bez/models/issues.php";
include_once DOKU_PLUGIN."bez/models/causes.php";

$causo = new Causes();
$issue_id = (int)$params[1];
$cause_id = (int)$params[3];

$isso = new Issues();
$template['issue'] = $isso->get($issue_id);
$template['cause'] = $causo->join($causo->getone($cause_id));
