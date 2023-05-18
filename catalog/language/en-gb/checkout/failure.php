<?php
// Heading
if (file_exists('../config.php')) {
  require_once('../config.php');
}

$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql= "SELECT * FROM oc_order ORDER BY order_id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
// output data of each row
  while($row = $result->fetch_assoc()) {
    $arr = $row;
  }
}
$sql= "SELECT * FROM oc_customer_transaction ORDER BY order_id = '".$arr['order_id']."'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  // output data of each row
    while($row = $result->fetch_assoc()) {
      $data = $row;
    }
}
$_['heading_title']        = '<b>Order Failed</b><br><br>Unfortunately, Your transaction has failed, so your order cannot be executed. Please try your purchase once more.<br><br>
      <b>Order details</b><br><br><div class="div2">
          ORDER NUMBER : '.$arr['order_id'].'<br>
          EASEBUZZ ID : '.$data['description'].'<br>
          DATE : '.$arr['date_modified'] .'<br>
          EMAIL : '.$arr['email'] .'<br>
          AMOUNT : '.sprintf("%.2f", $arr['total']) .'<br></div>
          
<style> 
  .div2 {
    font-family: Arial;
    width: 800px;
    height: 150px;  
    padding: 20px;
    border: 3px solid black;
    font-size: 22px;
  }
  .div {
    font-family: Arial;
    width: 800px;
    height: 260px;  
    padding: 20px;
    border: 3px solid black;
    font-size: 22px;
  }
</style><br>
<b>Billing Address</b><br><br>
<div class="div">
  <td>'.$arr["shipping_company"].'</td><br>
  <td>'.$arr["firstname"]." ".$arr["lastname"].'</td><br>
  <td>'.$arr['email'].'</td><br>
  <td>'.$arr['payment_address_1']."<br> ".$arr['payment_address_2'].'</td><br>
  <td>'.$arr['shipping_city'].", ".$arr['shipping_postcode'].' </td><br>
  <td>'.$arr['shipping_zone'].'</td><br>
  <td>'.$arr['email'].'</td><br>
  <td>&#128222;  '.$arr['telephone'].'</td><br>

</div>';

// Text
$_['text_basket']   = 'Shopping Cart';
$_['text_checkout'] = 'Checkout';
$_['text_failure']  = 'Failed Payment';
$_['text_message']  = '<p>There was a problem processing your payment and the order did not complete.</p>

<p>If the problem persists please <a href="%s">contact us</a> with the details of the order you are trying to place.</p>';
