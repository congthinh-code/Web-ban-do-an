<?php
$host='localhost';
$db='ten_database_cua_ban';
$user='root';
$pass='';
$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error) die("Kết nối thất bại: ".$conn->connect_error);
$conn->set_charset("utf8mb4");
?>
