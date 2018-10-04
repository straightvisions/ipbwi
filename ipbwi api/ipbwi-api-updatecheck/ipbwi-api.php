<?php
  header('Content-type: application/json');

  $arr = array (
    "version" => '4.1.8',
    "longversion" => 400018,
    "released" => 1513546168,
    "updateurl" => "https://invisionpower.com/files/file/8061-ipbwi-api-v4-extended-edition/"
  );

  echo json_encode($arr);
?>