<?php
require_once $_SERVER['DOCUMENT_ROOT'].'includes/config.php';
$product_id = ((isset($_POST['product_id']))? sanitize($_POST['product_id']) :'');
$available = ((isset($_POST['available']))? sanitize($_POST['available']) :'');
$quantity = ((isset($_POST['quantity']))? sanitize($_POST['quantity']) :'');


$item = array();
$item[] = array(
	'id'		=>$product_id,
	'quantity' 	=>$quantity,
	);

//gets the title of the from the database
$domain = ($_SERVER['HTTP_HOST'] != 'localhost')?'.'.$_SERVER['HTTP_HOST'] :false;
$query = $db->query("SELECT * FROM product WHERE id = '{$product_id}'");
$product = mysqli_fetch_assoc($query);
$_SESSION['success_flash'] = $product['name'] .' Was added to your cart.';


//check to see if the cart cookie exists
if ($cart_id != 0) {
	$cartQ = $db->query("SELECT * FROM cart WHERE id = '{$cart_id}'");
	$cart = mysqli_fetch_assoc($cartQ); 
	//get previous items in the cart table 
	$prevoius_items = json_decode($cart['item'],true);
	$item_match = 0;
	$new_items = array();
	foreach ($prevoius_items as $pitem) {
		if($item[0]['id'] == $pitem['id']){
			$pitem['quantity'] = $pitem['quantity'] + $item[0]['quantity'];
			if($pitem['quantity'] > $available){
				$pitem['quantity'] = $available;
			}
		$item_match = 1;
		}
		$new_items[] = $pitem;
	}
	if ($item_match != 1) {
		$new_items = array_merge($item,$new_items);
	}
	$items_json = json_encode($new_items);
	$cart_expire = date("Y-m-d H:i:s", strtotime("+30 days"));
	$db->query("UPDATE cart SET item = '{$items_json}',expire_date = '{$cart_expire}' WHERE id = '{$cart_id}'");
	setcookie(CART_COOKIE,'',1,'/',$domain,false);
	setcookie(CART_COOKIE,$cart_id,CART_COOKIE_EXPIRE,'/', $domain,false);
}else{ 
	//add the cart to the database and set cookie 
	$items_json = json_encode($item);
	$cart_expire = date("Y-m-d H:i:s", strtotime("+30 days"));
	$db->query("INSERT INTO cart (item,expire_date) VALUES('{$items_json}','{$cart_expire}')");
	$cart_id = $db->insert_id;
	setcookie(CART_COOKIE,$cart_id,CART_COOKIE_EXPIRE,'/', $domain,false);
}
?>
