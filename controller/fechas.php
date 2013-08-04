<?php
session_start();
$_SESSION['conectado'] = true;
include 'db_local.php';
//include 'db_web.php';

//CAMBIO DE ESTADO EN VIRTUEMART_ORDERS


if(isset($_GET['estado']))
	{
		//ECHO 'Hola, si que hay variables enviadas.<br>';
		//echo 'El estado a cambiar es: ' . $_GET['estado']['value'];
		//echo '<br>Y el id escondido es: ' . $_GET['id'];

		

		$sql = "UPDATE pike_virtuemart_orders SET order_status ='"  . $_GET['estado']. "'".
				'WHERE virtuemart_order_id = ' . $_GET['id'];

		


		$result = mysql_query($sql, $conexion);

		if($result)
		{
			
			Header ("Location: http://localhost:8888/mola/pedidos/pedidos.php");
			//Header ("Location: http://hastalascuatro.es/mola/pedidos/pedidos.php");
		}

	}


//FIN DE CAMBIO DE ESTADO




if( isset($_SESSION["conectado"]) || ( isset($_SESSION['enlinea']) && $_SESSION['enlinea'] == true ) ){

	
	$user = $_POST['username'];
	$pass = $_POST['password'];
	

	if($user == 'jose' && $pass == '1984' ){
		$_SESSION['conectado'] = true;
		$_SESSION['enlinea'] = true;
	
	 }else{
	 	$_SESSION['conectado'] = false;
	 }
	
	
}


?>




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
	if(isset($_SESSION['conectado'])  && $_SESSION['conectado'] == true || $_SESSION['enlinea'] == true){


	
	
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
			where pike_virtuemart_orders.created_on >= '".$inicio." 0:0:0' AND pike_virtuemart_orders.created_on <= '".$fin ."0:0:0'";


			$result = mysql_query($sql, $conexion);




		}else{
			echo "Faltan fechas";

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
				action="<? echo($_SERVER['PHP_SELF']); ?>"
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


