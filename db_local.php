<?php
$conexion = mysql_connect("localhost","neniah","neniah")
	or die ("Fallo en el establecimiento de la conexión");


mysql_select_db("mola")
		or die("Error en la selección de la base de datos");

?>