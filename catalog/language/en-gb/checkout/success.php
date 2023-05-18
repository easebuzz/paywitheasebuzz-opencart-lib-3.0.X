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
    // echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
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
$_['heading_title']        = '<b>Order received</b><br><br>Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be processing your order soon.<br><br>
        <b>Order details</b><br><br><div class="div2">
            ORDER NUMBER : '.$arr['order_id'].'<br>
            EASEBUZZ ID : '.$data['description'].'<br>
            DATE : '.$arr['date_modified'] .'<br>
            EMAIL : '.$arr['email'] .'<br>
            AMOUNT : '.sprintf("%.2f", $arr['total']).'<br></div>

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
$_['text_basket']          = 'Shopping Cart';
$_['text_checkout']        = 'Checkout';
$_['text_success']         = 'Success';
// $_['text_customer']        = '<p><br>Your order has been successfully processed!</p><p>You can view your order history by going to the <a href="%s">my account</a> page and by clicking on <a href="%s">history</a>.</p><p>If your purchase has an associated download, you can go to the account <a href="%s">downloads</a> page to view them.</p><p>Please direct any questions you have to the <a href="%s">store owner</a>.</p><p>Thanks for shopping with us online!</p>';
$_['text_guest']           = '<p>Please direct any questions you have to the <a href="%s">store owner</a>.</p><p>Thanks for shopping with us online!</p>';




