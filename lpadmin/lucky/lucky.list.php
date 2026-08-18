<?php
include_once('_common.php');

$params = $_GET;
$query = http_build_query($params);

$url = './result.php';

if ($query !== '') {
    $url .= '?' . $query;
}

goto_url($url);
