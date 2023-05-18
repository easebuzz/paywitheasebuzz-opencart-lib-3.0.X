<?php
class ControllerExtensionPaymentEasebuzz extends Controller {
    
    const PAY_BUTTON = 'image/pay01.png';
    
    public function index() {
        $data['easebuzz_button'] = self::PAY_BUTTON;
        $data['action'] = $this->url->link('extension/payment/easebuzz/pay');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/easebuzz')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/easebuzz', $data);
        } else {
            return $this->load->view('extension/payment/easebuzz', $data);
        }
    }
    
    public function pay() {
        if ($this->session->data['payment_method']['code'] == 'easebuzz') {
            require_once(__DIR__.'/lib/easebuzz-lib.php');
            $this->language->load('extension/payment/easebuzz');
            $this->load->model('checkout/order');
            
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $SALT=trim($this->config->get('payment_easebuzz_merchant_salt'));
            $MERCHANT_KEY=trim($this->config->get('payment_easebuzz_merchant_key'));
            $ENV=trim($this->config->get('payment_easebuzz_payment_mode'));
            
            $res=easepay_page(array(
                    'key' => $MERCHANT_KEY,
                    'txnid' => date('His') . $this->session->data['order_id'],
                    'amount' => $this->currency->format($order_info['total'], $order_info['currency_code'] , false, false),
                    'firstname' => html_entity_decode($order_info['firstname'] ." ". $order_info['lastname'], ENT_QUOTES, 'UTF-8'),
                    'email' => $order_info['email'],
                    'phone' => html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8'),
                    'productinfo' =>$this->session->data['order_id'],
                    'surl' =>  $this->url->link('extension/payment/easebuzz/callback'),
                    'furl' => $this->url->link('extension/payment/easebuzz/callback'),
                    'address1' => $order_info['payment_address_1'],
                    'address2' => $order_info['payment_address_2'],
                    'city' => $order_info['payment_city'],
                    'country' => $order_info['payment_iso_code_2']),
                    $SALT, $ENV);

            echo json_encode($res);
        }
    }
    public function callback() {
            //-----------------------------------------------------------------------------------------------------------------
            if (file_exists('../config.php')) {
                require_once('../config.php');
            }
            $conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            //-----------------------------------------------------------------------------------------------------------------    
        
        $data = array_merge($this->request->post, $this->request->get);
        try {
            if (empty($data)) {
                die('No parameter found');
            }

            if (isset($this->request->post['txnid'])) {
                $order_id = trim(substr(($this->request->post['txnid']), 6));
            } else {
                die('Illegal Access');
            }
            require_once(__DIR__.'/lib/easebuzz-lib.php');
            $SALT=trim($this->config->get('payment_easebuzz_merchant_salt'));         
            $result = response( $data, $SALT );
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($order_id);
            if($result['status']==1){
                if ($order_info) {
                                //-----------------------------------------------------------------------------------------------------------------
                        $data['1'] = 1;
                        $sql="INSERT INTO oc_customer_transaction (customer_transaction_id,customer_id, order_id, description,amount, date_added) values ('".$data['txnid']."','".$data['1']."','".$data['productinfo']."','".$data['easepayid']."','".$data['amount']."','".$data['addedon']."')";
                        $conn->query($sql);
                        $conn->close();
        
                //----------------------------------------------------------------------------------------------------------------
                    if($data["status"]=="success"){
                        $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_easebuzz_complete_status'));
                        $this->response->redirect($this->url->link('checkout/success', '', true));
                    }else{   
                        $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_easebuzz_cancelled_status'));
                        $this->response->redirect($this->url->link('checkout/failure', '', true));
                    }
                }
            }else{
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('easebuzz_cancelled_status'));
                $this->session->data['error'] = "Hash mismatch, Please try again.";                
                $this->response->redirect($this->url->link('checkout/checkout', '', true));
            }
        }catch (Exception $e) {
            $this->logger->write('OCR Notification: ' . $e->getMessage());
        }
    }

}
?>
