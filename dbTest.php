<?php
    $host = 'localhost';
    $user = 'r001';
    $pw = 'rdb001!!';
    $dbName = 'r001';
    $mysqli = new mysqli($host, $user, $pw, $dbName);
 
    if($mysqli){
        echo "MySQL 접속 성공";
    }else{
        echo "MySQL 접속 실패";
    }
?>