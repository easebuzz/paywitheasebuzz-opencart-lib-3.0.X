<?php


if(isset($_POST['Check_Refund_Status'])){
    // print_r($_POST['order_id']);
    $order_id = $_POST['order_id'];
    check_refund_status($order_id);
}
    function check_refund_status($order_id){
        if (file_exists('config.php')) {
            require_once('config.php');
        }
		$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		$key = "";
		$salt = "";
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
        $param_array['key'] = $key;
        $param_array['easebuzz_id'] = $arr['description'];
        $hash_sequence = "key|easebuzz_id";
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
        $params_array['easebuzz_id'] = $arr['description'];
        $params_array['hash'] = $hash_key;
        $params_array = http_build_query($params_array);
        $cURL = curl_init();
        curl_setopt_array( 
            $cURL, 
            array ( 
                CURLOPT_URL => 'https://dashboard.easebuzz.in/refund/v1/retrieve', 
                CURLOPT_POSTFIELDS => $params_array, 
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36', 
                CURLOPT_SSL_VERIFYHOST => 0, 
                CURLOPT_SSL_VERIFYPEER => 0 
            ) 
        );
        $res = curl_exec($cURL);
        //echo $res;
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
        echo "<strong>Transaction details for the given Order id.<br><br></strong>".
                "<table class='mycss' style="."width:30%".">
                    <tr><strong>Transaction Details:</strong><br></tr>
                    <tr>
                    &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Order Id : ".$order_id."<br>  
                    &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Total Amount : ".$result['amount']."<br>
                    &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Transaction status : ".$result['txnid']."<br>
                    &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Easebuzz ID : ".$result['easebuzz_id']."<br><br>
                    </tr>   
                </table>";
        $len = count($result[refunds]);
        echo "<strong>Refund details: </strong><br>";
        for($i = 1; $i <= $len; $i++){
            print_r("<strong>$i :   &#160;&#160;&#160;</strong>&#160;Easebuzz refund id : ".
                    $result['refunds'][$i-1]['refund_id']."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund status : ".
                    $result[refunds][$i-1][refund_status]."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund id : ".
                    $result[refunds][$i-1][merchant_refund_id]."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Merchant refund date : ".
                    $result[refunds][$i-1][merchant_refund_date]."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund amount : ".
                    $result[refunds][$i-1][refund_amount]."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Arn Number : ".
                    $result[refunds][$i-1][arn_number]."<br>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Refund settled date : ".
                    $result[refunds][$i-1][refund_settled_date]."<br><br>");
        }
        ?>
        <button onclick="history.back()">Go Back</button>
        <?php
        exit();
    }
?>
<h1> Check Refund status api</h1>
<form action="check_refund_status.php" method="post" autocomplete="off">
  <p>Order ID  :  <input type="number" name="order_id" /></p>
  <input type="submit" name="Check_Refund_Status" value="Check Refund Status" />

</form>
