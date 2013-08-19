<?php
session_start();
session_unset();
session_destroy();
//header('Location: http://molamarket.com/pventa/index.php');
header('Location: http://localhost:8888/mola/pventa/index.php');

?>

