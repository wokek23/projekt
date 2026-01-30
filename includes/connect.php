<?php
$db_server = "localhost";
$db_user = "szczerbal";
$db_pass = "8AE2kgccMYEWsVM";
$db_base = "szczerbal";

try{
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_base);
} catch (Exception $e){
    die("Wystąpił błąd");
}
?>