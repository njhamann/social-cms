<?php
header('Content-type: application/json');
require('../lib/html_parser/simple_html_dom.php');
$id = $_REQUEST['id'];
$url = $_REQUEST['vine_url'];
$html = file_get_html($url);
$e = $html->find('video', 0);
$info = array(
    'id' => $id,
    'screen_url' => $e->poster,
    'video_url' => $e->find('source', 0)->src
);
echo json_encode($info);
?>
