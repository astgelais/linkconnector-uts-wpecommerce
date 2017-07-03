<?php
/*
Plugin Name: LinkConnector UTS - WP eCommerce
Description: LinkConnector Universal Tracking Solution code for WP eCommerce
Version:     1.0
Author:      Aaron St. Gelais
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* Start LinkConnector WordPress Menu Code */
add_action( 'admin_menu', 'linkconnector_uts_menu' );

function linkconnector_uts_menu() {
	
	//Menu items
  $page_title = 'LinkConnector UTS Plugin';
  $menu_title = 'LinkConnector UTS Plugin';
  $capability = 'manage_options';
  $menu_slug  = 'linkconnector-uts';
  $function   = 'linkconnector_uts_page';
  $icon_url   = 'dashicons-media-code';

	//Add LinkConnector UTS to the WP Dashboard Menu
  add_menu_page( $page_title, $menu_title,  $capability,  $menu_slug,  $function,  $icon_url,  $position );

}

function linkconnector_uts_page() {
	
	//Create HTML form to hold LinkConnector UTS option values
	?>
  <h1>LinkConnector Universal Tracking Solution (UTS)</h1>
  <p>Do not change the Campaign Group ID or Event ID values unless specified by your LinkConnector Merchant Representative.</p>
  <p>If you need help, please contact Merchant Relations - 9194685150 ext. 1</p>
  <form method="post" action="options.php">
    <?php settings_fields( 'linkconnector-uts-settings' ); ?>
    <?php do_settings_sections( 'linkconnector-uts-settings' ); ?>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Event ID:</th>
      <td><input type="text" name="linkconnector_uts_eid" value="<?php echo get_option( 'linkconnector_uts_eid' ); ?>"/></td>
      </tr>
      <tr valign="top">
      <th scope="row">Campaign Group ID:</th>
      <td><input type="text" name="linkconnector_uts_cgid" value="<?php echo get_option( 'linkconnector_uts_cgid' ); ?>"/></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form> 
<?php

}

add_action( 'admin_init', 'linkconnector_uts_settings' );

function linkconnector_uts_settings() {
	
  register_setting( 'linkconnector-uts-settings', 'linkconnector_uts_cgid' );
  register_setting( 'linkconnector-uts-settings', 'linkconnector_uts_eid' );
  
}

add_action( 'admin_notices', 'admin_notices' );

function admin_notices() {
	//Check settings for Event ID and Campaign Group ID
	$settings[cgid] = get_option( 'linkconnector_uts_cgid' );
	$settings[eid] = get_option( 'linkconnector_uts_eid' );
	if ( ! isset( $settings[cgid] ) || empty( $settings[cgid] ) ) {
		echo ( '<div class="error"><p>' . __( 'LinkConnector UTS WP eCommerce Merchant Tracking requires your Campaign Group ID before it can start tracking sales. <a href="admin.php?page=linkconnector-uts">Do this now</a>', 'linkconnector-uts' ) . '</p></div>' );
	}
	if ( ! isset( $settings[eid] ) || empty( $settings[eid] ) ) {
		echo ( '<div class="error"><p>' . __( 'LinkConnector UTS WP eCommerce Merchant Tracking requires your Event ID before it can start tracking sales. <a href="admin.php?page=linkconnector-uts">Do this now</a>', 'linkconnector-uts' ) . '</p></div>' );
	}
	
}   
/* End LinkConnector WordPress Menu Code */

/* Start UTS Landing Code */
add_action( 'wp_footer', 'linkconnector_uts_landing' );

function linkconnector_uts_landing( ) {

//Get the cgid
$cgid = get_option('linkconnector_uts_cgid');

$lc_lp_call = <<<LCCALL
<script type="text/javascript" src="//www.linkconnector.com/uts_lp.php?cgid=$cgid"></script>
LCCALL;

if($cgid) {echo $lc_lp_call;} else {return;}

}

/* End UTS Landing Code */

/* Start UTS Confirm Code */
add_filter( "wpsc_pre_transaction_results", "linkconnector_uts_confirm" );

function linkconnector_uts_confirm( ) {

//Get the cgid and eid
$cgid = get_option('linkconnector_uts_cgid');
$eid = get_option('linkconnector_uts_eid');

//Get the sessionid
if ( isset( $_GET['sessionid'] ) )
$sessionid = $_GET['sessionid'];

//Lookup purchase information based upon the sessionid
$purchase_log_object = new WPSC_Purchase_Log( $sessionid, 'sessionid' );

//Grab purchase data
$purchase_log = $purchase_log_object->get_data();

$order_id = $purchase_log[id];
$order_base_total = $purchase_log[totalprice];
$order_coupon = $purchase_log[discount_data];
$order_discount = $purchase_log[discount_value];

//Grab the WP eCommerce base currency code
global $wpdb;
$table_prefix = $wpdb->prefix;

$rs = @mysql_query("SELECT code FROM ". $table_prefix ."wpsc_currency_list, ". $table_prefix ."options WHERE wp_options.option_value = wp_wpsc_currency_list.id and wp_options.option_name = 'currency_type'");

$order_currency = "USD";

if(!$rs){
	$error_report = mysql_error();
} else {
	$numrows = mysql_num_rows($rs);
	if($numrows == 0) {
		$order_currency = "USD";
	} else {
		while ($row = mysql_fetch_assoc($rs)){
			$order_currency = $row['code'];
		}
	}
}

//Grab required LinkConnector UTS variables
$ordervars = <<<ORDERVARS
<script type="text/javascript">
var uts_orderid = "$order_id"; // the Order ID
var uts_saleamount = "$order_base_total"; // the Order total after discounts
var uts_coupon = "$order_coupon"; // Enter Coupon Code
var uts_discount = "$order_discount"; // Enter Discount amount
var uts_currency = "$order_currency";
var uts_eventid = "$eid"; // Enter LinkConnector EventID
</script>
ORDERVARS;

echo $ordervars;

/* Set Product Variables */
$items = $purchase_log_object->get_cart_contents();

/****** initialize javascript array ********/

$order_items = <<<ORDER_ITEMS
<script type="text/javascript">
var uts_products = new Array();
ORDER_ITEMS;

/****** loop thru cart ********/
$j = 0;
foreach($items as $itemID) {
$itemDetails = new wpsc_cart_item($items[$j]->prodid,'','');
$quantity = $items[$j]->quantity;
$category = implode(",",$itemDetails->category_list);
$order_items .= <<<ORDER_ITEMS
uts_products[$j] = new Array(); 
uts_products[$j][0] = "$itemDetails->sku";
uts_products[$j][1] = "$itemDetails->product_name";
uts_products[$j][2] = "$quantity";
uts_products[$j][3] = "$itemDetails->unit_price";
uts_products[$j][4] = "$category";
ORDER_ITEMS;
$j++;
}

/****** close javascript array declaration *****/
$order_items .= <<<ORDER_ITEMS
</script>
ORDER_ITEMS;
echo $order_items;

$lc_call = <<<LCCALL
<script type="text/javascript" src="//www.linkconnector.com/uts_tm.php?cgid=$cgid"></script>
LCCALL;

if($cgid) {echo $lc_call;} else {return;}

}
/* End UTS Confirm Code */

?>