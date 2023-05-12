<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
?>
<div class="product_meta">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>

	<?php endif; ?>

	<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>

	<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

	<?php

	//SAMSUNG CARACOL QUITO						001
	//Mayoristas Cuenca - Codigo Almacen		002
	//PADRE AGUIRRE								003
	//GADGETS									004*
	//MATRIZ CUENCA								005*
	//MAYORISTAS QUITO							006
	//STOCK DE GARANTIAS Y REPUESTOS			007*
	//CONSIGNACIÓN								008*
	//SAMSUNG BAHIA								009
	//LUIS CORDERO								010
	//SAMSUNG CUENCA							011
	//BODEGA DE CUARENTENA						012*
	//ACCESORIOS								013
	//ME COMPRAS SAMSUNG						014
	//BLU BAHIA									015
	//SAMSUNG MALL GUAYAQUIL					016
	//SAMSUNG MALL CUENCA						017
	//CELISTIC 									019*

	$listaBodegas = array("001", "002", "003", "006", "009", "010", "011", "013", "014", "015", "016", "017");
	//var_dump($listaBodegas);

	//Producto con Variaciones.
	if ($product->is_type('variable')) {

		$product = wc_get_product($product->get_id());
		$children_ids = $product->get_children();
		$longitud = count($children_ids);
		for ($i = 0; $i < $longitud; $i++) {
			//saco el valor de cada elemento
			$product2 = wc_get_product($children_ids[$i]);
			//echo $product2->get_sku();

			$p_variable = $product2->get_sku();




			$stockList = array();
			foreach ($listaBodegas as $bodega) {
				//echo $bodega . "\n";

				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_URL => 'http://191.100.22.203:8091/LIDENAR.asmx/FILTRO_CODIGO?ItemCode=' . $p_variable . '&CodigoAlmacen=' . $bodega . '',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'GET',
				));

				$response = curl_exec($curl);
				$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				curl_close($curl);
				//echo $response;

				$json = json_decode($response, true);
				$cantidadAux = 0;
				$cantidadAux = $json[0]['DisponibleParaOfrecer'];
				//echo $cantidadAux;
				//echo "<br>";

				array_push($stockList, $cantidadAux);
			}

			//print_r($stockList);
			$cantidad = 0;
			foreach ($stockList as $stock) {
				$cantidad += $stock;
			}

			echo "El stock total es: " . $cantidad;

			//Consumir los precios del sistema HT

			$curlPrecios = curl_init();

			curl_setopt_array($curlPrecios, array(
				CURLOPT_URL => "http://172.22.106.233:8080/grunsoft/rest/mecompras/api/v1/mecompras",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => "{\n    \"codProducto\": \"$p_variable\"\n}\n",
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json"
				),
			));

			$responsePrecios = curl_exec($curlPrecios);
			curl_close($curlPrecios);

			$jsonPrecios = json_decode($responsePrecios, true);

			//Iniciamos

			if ($httpcode == 200) {

				//$cantidad = $json['cantidad'];
				//echo $cantidad;
				//Precio NO tachado mecompras PVP
				$precio1 = $jsonPrecios['PrecioPublico'];
				//Precio tachado mecompras TC
				$precio2 = $jsonPrecios['PrecioMilitar'];
				//Cantidad mayor igual a 10 - Con stock
				if ($cantidad >= 3) {
					//require_once('wp-config.php');
					//$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), constant("DB_NAME"));
					// Check coneexion.
					//            if ($conn->connect_error) {
					//                die("Connection failed: " . $conn->connect_error);
					//            }
					//Revisamos credenciales
					//            echo DB_HOST;
					//            echo DB_USER;
					//            echo DB_PASSWORD;
					//            echo DB_NAME;

					//Verificar que llega el correo.
					if (isset($p_variable)) {
						$sku_texto = $p_variable;

						//Obtener product_id de SKU: devuelve nulo si no se encuentra
						$product_id = wc_get_product_id_by_sku($sku_texto);
						//Configurar objeto de producto WooCommerce
						$product = wc_get_product($product_id);
						//$product->set_price(800.00); 
						$product->set_regular_price($precio2);
						$product->set_sale_price($precio1);
						//Gestionar el inventario
						$product->set_manage_stock(false);
						$product->set_stock_status('instock');
						//$product->set_stock_status('outofstock');
						wc_delete_product_transients($product->get_id());
						//echo $product->get_id();
						$product->save();
						//echo $texto;
						//Insertar en la Base de Datos
						//$sql = "update wp_postmeta set meta_value = 'instock' where post_id = (select post_id from wp_postmeta where meta_value like '$texto') and  meta_key like '%_stock_status%'";
						//echo $sql;
						//
						//if ($conn->query($sql) === true) {
						//echo "Actualizado==>". $cantidad;
						//} else {
						//Imprimir el error si no se guarda en la base de datos
						//die("Error al insertar datos: " . $conn->error);
						//echo "No";
						//}
						//Cerramos la conexión.
						//$conn->close();
					}
					//Cantidad menor igual a 9 - Sin Stock
				} elseif ($cantidad <= 2) {

					//require_once('wp-config.php');
					//$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), constant("DB_NAME"));
					// Check coneexion.
					//    if ($conn->connect_error) {
					//        die("Connection failed: " . $conn->connect_error);
					//    }
					//Revisamos credenciales
					//    echo DB_HOST;
					//    echo DB_USER;
					//    echo DB_PASSWORD;
					//    echo DB_NAME;

					//Verificar que llega el correo.
					if (isset($p_variable)) {
						$sku_texto = $p_variable;
						// Obtener product_id de SKU: devuelve nulo si no se encuentra
						$product_id = wc_get_product_id_by_sku($sku_texto);
						// Configurar objeto de producto WooCommerce
						$product = wc_get_product($product_id);

						//$product->set_price(700); 
						$product->set_regular_price($precio2);
						$product->set_sale_price($precio1);
						//$product->set_stock_status('instock');
						//Gestionar el inventario
						$product->set_manage_stock(false);
						$product->set_stock_status('outofstock');
						//$product->set_stock_quantity($cantidad);
						wc_delete_product_transients($product->get_id());
						//echo $product->get_id();
						$product->save();

						//Insertar en la Base de Datos
						//$sql = "update wp_postmeta set meta_value = 'outofstock' where post_id = (select post_id from wp_postmeta where meta_value like '$texto') and  meta_key like '%_stock_status%'";
						//echo $sql;
						//if ($conn->query($sql) === true) {
						//echo "Actualizado==>" . $cantidad;
						//} else {
						//Imprimir el error si no se guarda en la base de datos
						//die("Error al insertar datos: " . $conn->error);
						//echo "No";
						//}
						//Cerramos la conexión.
						//$conn->close();

					};
					//
				};
			} else {
				echo "Verificar SKU";
			}

			//echo "<br>";
		};
	}
	//Producto simple.
	else {
		$p_simple = $product->get_sku();
		//Consumiendo el stock de SAP.
		$stockList = array();
		foreach ($listaBodegas as $bodega) {
			//echo $bodega . "\n";
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'http://191.100.22.203:8091/LIDENAR.asmx/FILTRO_CODIGO?ItemCode=' . $p_simple . '&CodigoAlmacen=' . $bodega . '',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',
			));
			$response = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			//echo $response;
			$json = json_decode($response, true);
			$cantidadAux = 0;
			$cantidadAux = $json[0]['DisponibleParaOfrecer'];
			//echo $cantidadAux;
			//echo "<br>";

			array_push($stockList, $cantidadAux);
		}

		//print_r($stockList);
		$cantidad = 0;
		foreach ($stockList as $stock) {
			$cantidad += $stock;
		}

		echo "El stock total es: " . $cantidad;
		//Consumiendo los precios del sistema HT.

		$curlPrecios = curl_init();

		curl_setopt_array($curlPrecios, array(
			CURLOPT_URL => "http://172.22.106.233:8080/grunsoft/rest/mecompras/api/v1/mecompras",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\n    \"codProducto\": \"$p_simple\"\n}\n",
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json"
			),
		));

		$responsePrecios = curl_exec($curlPrecios);
		curl_close($curlPrecios);
		$jsonPrecios = json_decode($responsePrecios, true);

		//Iniciamos todo.

		if ($httpcode == 200) {
			//$cantidad = $json['cantidad'];
			//Precio NO tachado mecompras PVP
			$precio1 = $jsonPrecios['PrecioPublico'];
			//Precio tachado mecompras TC
			$precio2 = $jsonPrecios['PrecioMilitar'];
			//Cantidad mayor igual a 10 - Con stock
			if ($cantidad >= 3) {
				//require_once('wp-config.php');
				//$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), constant("DB_NAME"));
				// Check coneexion.
				//            if ($conn->connect_error) {
				//                die("Connection failed: " . $conn->connect_error);
				//            }
				//Revisamos credenciales
				//            echo DB_HOST;
				//            echo DB_USER;
				//            echo DB_PASSWORD;
				//            echo DB_NAME;

				//Verificar que llega el correo.
				if (isset($p_simple)) {
					//$texto = $p_simple;
					//$product->set_price(700); 
					$product->set_regular_price($precio2);
					$product->set_sale_price($precio1);
					//Gestionar el inventario
					$product->set_manage_stock(false);
					$product->set_stock_status('instock');
					//$product->set_stock_status('outofstock');
					wc_delete_product_transients($product->get_id());
					//echo $product->get_id();
					$product->save();
					//echo $texto;
					//Insertar en la Base de Datos
					//$sql = "update wp_postmeta set meta_value = 'instock' where post_id = (select post_id from wp_postmeta where meta_value like '$texto') and  meta_key like '%_stock_status%'";
					//echo $sql;
					//
					//if ($conn->query($sql) === true) {
					// echo "Actualizado==>" . $cantidad;
					//} else {
					//Imprimir el error si no se guarda en la base de datos
					//die("Error al insertar datos: " . $conn->error);
					//echo "No";
					//}
					//Cerramos la conexión.
					//$conn->close();
				}
				//Cantidad menor igual a 9 - Sin Stock
			} elseif ($cantidad <= 2) {
				//require_once('wp-config.php');
				//$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), constant("DB_NAME"));
				// Check coneexion.
				//    if ($conn->connect_error) {
				//        die("Connection failed: " . $conn->connect_error);
				//    }
				//Revisamos credenciales
				//    echo DB_HOST;
				//    echo DB_USER;
				//    echo DB_PASSWORD;
				//    echo DB_NAME;

				//Verificar que llega el correo.
				if (isset($p_simple)) {
					//$texto = $p_simple;
					//$product->set_price(700); 
					$product->set_regular_price($precio2);
					$product->set_sale_price($precio1);
					//$product->set_stock_status('instock');
					//Gestionar el inventario
					$product->set_manage_stock(false);
					$product->set_stock_status('outofstock');
					//$product->set_stock_quantity($cantidad);
					wc_delete_product_transients($product->get_id());
					//echo $product->get_id();
					$product->save();
					//Insertar en la Base de Datos
					//$sql = "update wp_postmeta set meta_value = 'outofstock' where post_id = (select post_id from wp_postmeta where meta_value like '$texto') and  meta_key like '%_stock_status%'";
					//echo $sql;
					//if ($conn->query($sql) === true) {
					// echo "Actualizado==>". $cantidad;
					//} else {
					//Imprimir el error si no se guarda en la base de datos
					//die("Error al insertar datos: " . $conn->error);
					//echo "No";
					//}
					//Cerramos la conexión.
					//$conn->close();
				};
				//
			};
		} else {
			echo "Verificar SKU";
		}
	}


	?>

</div>
