<?php
session_start();
include 'db_local.php';
require('class.phpmailer.php');

if(isset($_POST['username']) && isset($_POST['password'])){

	$user = $_POST['username'];
	$pass = $_POST['password'];

	$consulta = "SELECT id_pventa FROM pike_virtuemart_pventa 
				WHERE user='".$user."' AND pass='".$pass. "'";

	//echo $consulta;

	$re_consulta = mysql_query($consulta);

	$registro = mysql_num_rows($re_consulta);

	if($registro > 0 )
	{
		//echo "Acceso correcto";
		while($row =mysql_fetch_array($re_consulta)){
			$id_pventa = $row['id_pventa'];
			//echo "MI PVENTA -- >". $id_pventa;
			$_SESSION['id_pventa'] = $id_pventa;
		}

	}else{
		echo "ERROR LOGIN";
	}
}



//CAMBIO DE ESTADO EN VIRTUEMART_ORDERS


if(isset($_GET['estado']))
	{

	
		$sql = "UPDATE pike_virtuemart_orders SET order_status ='"  . $_GET['estado']. "'".
				'WHERE virtuemart_order_id = ' . $_GET['id'];

		
		$result = mysql_query($sql, $conexion);

		$order_id = $_GET['id'];
		$estado = $_GET['estado'];




		if($result)
		{
			if($estado == 'R' || $estado == 'T'){
				//OBTENER DATOS EL USER
					$user_sql = 'select pike_users.id, pike_users.email, pike_users.name 
				from pike_virtuemart_orders 
				INNER JOIN pike_users
				ON pike_virtuemart_orders.virtuemart_user_id = pike_users.id
				where virtuemart_order_id=' . $order_id;

				$re_user = mysql_query($user_sql);

				while($row_user = mysql_fetch_array($urser_sql)){
					$destino = $row_user['email'];
					$user_name = $row_user['name'];

					//ENVIO DE EMAIL


						$mail->AddReplyTo($origen, 'Mola Market Pedidos');
						$mail->AddAddress($destino, 'User Name');
						$mail->SetFrom($origen, 'Mola Market Pedidos');
						
						 
						$mail->Subject  = $asunto;
						$mail->Body     = $cuerpo;
						$mail->WordWrap = 50;

						 
						if(!$mail->Send()) {
							echo 'Message was not sent.';
							echo 'Mailer error: ' . $mail->ErrorInfo;
						} else {
							echo 'Message has been sent.';
						}


				}
				



			}

		}
	}//FIN DE CAMBIO DE ESTADO


?>
<html>

<head>
	
  <title>MolaMarket - Pedidos </title>
  <link rel="stylesheet" href="js/jquery.css" />
  <script src="js/jquery.js"></script>
  <script src="js/ui.js"></script>
  <link rel="stylesheet" href="css/foundation.css" type="text/css" media="screen"/>
  <script type="text/javascript" src="css/responsive-tables.js"></script>
  
  <script>
  $(function() {
    $( ".accordion" ).accordion({
      collapsible: true,
      active:false
      
    });
  });
  </script>

	</head>


	<body>

		<nav class="horizontal">
			<ul>
				<li><a class='button radius large' href='pedidos.php'>Pedidos</a></li>
				
				<li><a class='button radius secondary large' href='lock.php'>
					Salir</a></li>
				
			</lu>
				
		</nav>
  <script>
  $(function() {
    $( ".datepicker" ).datepicker({
    	 dateFormat: 'yy-mm-dd'
    });
  });
  </script>

		<section class='fechas'>
			<form class="custom" action="pedidos.php?mn=f" method="post">
				Fecha Inicio<input class='datepicker' name='inicio' class='large-4' id='inicio'>
				Fecha Fin<input class='datepicker' name='fin' class='large-4' id='fin'>
				<input type='submit' value='Mostrar' class="button large nice radius">
			</form>
		</section>
<?php
	//conectar con base datos y tabla de select

	if(isset($_SESSION['id_pventa'])){


	
	
		if(isset($_POST['inicio']) && isset($_POST['fin']) ){
			$inicio = $_POST['inicio'];
			$fin = $_POST['fin'];
			


			$sql = "select distinct pike_virtuemart_orders.order_number,
			pike_virtuemart_orders.virtuemart_user_id, 
			pike_virtuemart_orders.virtuemart_order_id,
			 pike_virtuemart_orders.order_status, 
			pike_virtuemart_orders.virtuemart_paymentmethod_id, 
			pike_virtuemart_orderstates.order_status_name as estado,
			pike_virtuemart_orders.created_on,
			pike_virtuemart_shipmentmethods_es_es.shipment_name as shipment, 
			pike_virtuemart_paymentmethods_es_es.payment_name as payment
			

			from pike_virtuemart_orders
			
			INNER JOIN pike_virtuemart_orderstates
			on pike_virtuemart_orders.order_status = pike_virtuemart_orderstates.order_status_code
			INNER JOIN pike_virtuemart_paymentmethods_es_es
			on pike_virtuemart_orders.virtuemart_paymentmethod_id = pike_virtuemart_paymentmethods_es_es.virtuemart_paymentmethod_id
			INNER JOIN pike_virtuemart_shipmentmethods_es_es
			on pike_virtuemart_orders.virtuemart_shipmentmethod_id = pike_virtuemart_shipmentmethods_es_es.virtuemart_shipmentmethod_id
			where pike_virtuemart_orders.created_on >= '".$inicio." 0:0:0' AND pike_virtuemart_orders.created_on <= '".$fin ."0:0:0' AND ".
			"pike_virtuemart_shipmentmethods_es_es.virtuemart_shipmentmethod_id=". $_SESSION['id_pventa'];

			//echo "El ip de venta es -> " . $_SESSION['id_pventa'];
			$result = mysql_query($sql, $conexion);




		}else{
			echo "Por favor, elija rango de fechas para mostrar los pedidos.";

		}
	

		
?>
	<div class="responsive large-offset-0 mitabla">
		<table border=0 id='accordion'>
		<tr class="titulo">
			<th>Num Pedido</th>
			<th class='large-3'>Productos</th>	
			<th class='large-2'>Estado</th>
			<th>Metodo Pago</th>
			<th>Punto Venta</th>

			<th>Fecha Pedido</th>
		
		<tr>
<?php
		while($row = mysql_fetch_array($result)) {
			echo '<tr>';
			echo '<td >'.$row['order_number'].'</td>';
			echo '<td>';
			echo '<div class="accordion">';
			echo '<h5>Detalle Productos</h5>';
			echo '<div><p>';
				$sql3 = "select order_item_name, product_quantity, product_final_price 
							from pike_virtuemart_order_items
							where virtuemart_order_id = " . $row['virtuemart_order_id'];
				$result3 = mysql_query($sql3, $conexion);
				while($item = mysql_fetch_array($result3)){
					echo $item['order_item_name'] . ' x '. $item['product_quantity']. 
					' = '.round($item['product_final_price'],2).'$';
					echo '<br>';

				}

			echo '<p></div></div>';
			echo '</td>';
			echo '<td >';
				//insertar los botones de edition estado
			echo $row['estado'];
?>
			<form 
				action="pedidos.php"
				method="get"
				enctype="text/plain"
				onsubmit="return confirm('Seguro que quiere enviar el formulario')"
				name=<?php echo $row['virtuemart_order_id']; ?>
			>
<?php
			echo "<select name='estado' class='tiny button dropdown secondary'>";
			
				$sql2 = "select order_status_name as estado, 
				 		order_status_code as code from pike_virtuemart_orderstates";
				$result2 = mysql_query($sql2, $conexion);
				while($option = mysql_fetch_array($result2)){
					echo "<option value=".$option['code'].">".$option['estado']. '</option>';
				}
				

			echo '</select>';
?>

			<input type="hidden" value="<?php echo $row['virtuemart_order_id'];?>" name="id">
			<input type="submit" value="Cambiar estado de Pedido" class="tiny button radious secondary">
		</form>
<?php

			echo '</td>';
			echo '<td >'.$row['payment'].'</td>';
			echo '<td >'.$row['shipment'].'</td>';
			echo '<td >'.$row['created_on'].'</td>'; 
			echo '</tr>';
		}

		echo "</table>";

		//cerramos la base de datos
		mysql_close($conexion);
?>

	</div>
<?php


	}else{
		echo "Login incorrecto, por favor vuelva a intentarlo";
		echo "<a href='index.php'>Inicio</a>";
	}
		
		

		
	
?>





	</body>
</html>