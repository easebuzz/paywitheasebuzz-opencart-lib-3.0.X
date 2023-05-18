<?php

    if(isset($_POST['submit'])){
      $order_id = $_POST['order_id'];
      $refund_amount = $_POST['refund_amount'];
      process_refund($order_id, $refund_amount);
    }
	
   	if(isset($_POST['back'])){
		header("Location:process_refund.php");
	}

	function process_refund($order_id, $refund_amount){
        if (file_exists('config.php')) {
            require_once('config.php');
        }

		$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		$amount = number_format((float)$refund_amount, 2, '.', '');
		$key = "";
		$salt = "";
		$ENV = "";
        $sql= "SELECT value FROM " . DB_PREFIX . "setting where code = '".'payment_easebuzz'."'and `key` = '".'payment_easebuzz_merchant_key'."';";
        $result = $conn->query($sql);
		while($row = $result->fetch_assoc()) {
			$key = $row['value'];
		}
        $sql = "SELECT value FROM " . DB_PREFIX  ."setting where code = '".'payment_easebuzz'."'and `key` = '".'payment_easebuzz_merchant_salt'."';";
		$result = $conn->query($sql);
		while($row = $result->fetch_assoc()) {
			$salt = $row['value'];
		}
        $sql = "SELECT value FROM " . DB_PREFIX . "setting where code = '".'payment_easebuzz'."'and `key` = '".'payment_easebuzz_payment_mode'."'";
		$result = $conn->query($sql);
		while($row = $result->fetch_assoc()) {
			$ENV = $row['value'];
		}
		$sql= "SELECT description FROM " . DB_PREFIX . "customer_transaction  where order_id = '".$order_id."'";
		$arr['description'] = [];
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
			  $arr = $row;
			}
		}else{
			echo "<script>alert('Order Id not found');</script>";
			header("Refresh:0");
			exit();
		}
		$merchant_refund_id = (int)(date("his").$order_id);
		$url_link = 'https://dashboard.easebuzz.in/transaction/v2/refund';
		$param_array['key'] = $key;
		$param_array['merchant_refund_id'] = $merchant_refund_id;
		$param_array['easebuzz_id'] = $arr['description'];
		$param_array['refund_amount'] = $amount;
		$hash_sequence = "key|merchant_refund_id|easebuzz_id|refund_amount";
		// make an array or split into array base on pipe sign.
		$hash_sequence_array = explode( '|', $hash_sequence );
		$hash = null;
		foreach($hash_sequence_array as $value ) {
		$hash .= isset($param_array[$value]) ? $param_array[$value] : '';
		$hash .= '|';
		}
		$hash .= $salt;
		$hash_key = strtolower( hash('sha512', $hash) );
		$params_array = array();
		$params_array['key'] = $key;
		$params_array['merchant_refund_id'] = $merchant_refund_id;
		$params_array['easebuzz_id'] = $arr['description'];
		$params_array['refund_amount'] = $amount;
		$params_array['hash'] = $hash_key;
		$params_array = http_build_query($params_array);
		$cURL = curl_init();
		// Set multiple options for a cURL transfer.
		curl_setopt_array( 
			$cURL, 
			array ( 
				CURLOPT_URL => 'https://dashboard.easebuzz.in/transaction/v2/refund', 
				CURLOPT_POSTFIELDS => $params_array, 
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true, 
				CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36', 
				CURLOPT_SSL_VERIFYHOST => 0, 
				CURLOPT_SSL_VERIFYPEER => 0 
			) 
		);
		$res = curl_exec($cURL);
		//check there is any error or not in curl execution.
		if( curl_errno($cURL) ){
			$cURL_error = curl_error($cURL);
			if( empty($cURL_error) )
				$cURL_error = 'Server Error';
			
			return array(
				'curl_status' => 0, 
				'error' => $cURL_error
			);
		}
		$res = trim($res);
		$result = json_decode($res, true);

		if($result['status'] == false){
			print_r("<b>Refund can not raised because ");
			print_r("<b>".$result['reason']."</b><br>");
			print_r("Refund amount : ".$amount."<br>");
			print_r("order id : ".$order_id."<br><br>");
			?>
				<button onclick="history.back()">Go Back</button>
			<?php
			exit();
		}else{
			print_r("<b>Refund has been successFully raised.<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund Amount : ". sprintf("%.2f", $result['refund_amount'])."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund status : queued"."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund id : ".$result['refund_id']."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Easebuzz id : ".$result['easebuzz_id']."</b><br><br>");
			// $sql = "INSERT INTO " . DB_PREFIX . "return_history(return_id, return_status_id, notify, comment, date_added) values('".$merchant_refund_id."',0,1,'".$result['refund_id'].",".sprintf("%.2f", $result['refund_amount'])."',now())";
			// $conn->query($sql);
			?>
		        <button onclick="history.back()">Go Back</button>
			<?php
			exit();
		}
	}   
?>
<h1> Refund Api</h1>
<form action="process_refund.php" method="post" autocomplete="off">
	<p>Please enter the order id and amount.</p>
	<p>Order ID : &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;  <input type="number" name="order_id" /></p>
	<p>Refund amount : &#160;<input type="text" name="refund_amount" /></p>
	<input type="submit" name="submit" value="Submit" /><br><br>
</form>
