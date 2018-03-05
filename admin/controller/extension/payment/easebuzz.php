<?php

class ControllerExtensionPaymentEasebuzz extends Controller {
    private $error = array();
    private $settings = array();

    public function index() {
        $this->load->language('extension/payment/easebuzz');
        $this->document->setTitle('Easebuzz Payment Method Configuration');
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_easebuzz', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }
        if ($this->error) {
            $data['error_warning'] = implode("<br/>", $this->error);
        } else
            $data['error_warning'] = "";

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_edit'] = $this->language->get('text_edit');

        $data['entry_currency'] = $this->language->get('entry_currency');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_merchant_key'] = $this->language->get('entry_merchant_key');
        $data['entry_merchant_salt'] = $this->language->get('entry_merchant_salt');
        $data['entry_payment_mode'] = $this->language->get('entry_payment_mode');
        $data['entry_complete_status'] = $this->language->get('entry_complete_status');
        $data['entry_cancelled_status'] = $this->language->get('entry_cancelled_status');
        $data['entry_new_status'] = $this->language->get('entry_new_status');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');

        $data['help_merchant_key'] = $this->language->get('help_merchant_key');
        $data['help_merchant_salt'] = $this->language->get('help_merchant_salt');
        $data['help_payment_mode'] = $this->language->get('help_payment_mode');
        $data['help_total'] = $this->language->get('help_total');

        //Errors
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['error_merchant_key'] = isset($this->error['merchant_key']) ? $this->error['merchant_key'] : '';
        $data['error_merchant_salt'] = isset($this->error['merchant_salt']) ? $this->error['merchant_salt'] : '';

        //Zones, order statuses
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        //Settings
        $data['payment_easebuzz_total'] = isset($this->request->post['payment_easebuzz_total']) ?
            $this->request->post['payment_easebuzz_total'] : $this->config->get('payment_easebuzz_total');

        $data['payment_easebuzz_geo_zone_id'] = isset($this->request->post['payment_easebuzz_geo_zone_id']) ?
            $this->request->post['payment_easebuzz_geo_zone_id'] : $this->config->get('payment_easebuzz_geo_zone_id');

        $data['payment_easebuzz_status'] = isset($this->request->post['payment_easebuzz_status']) ?
            $this->request->post['payment_easebuzz_status'] : $this->config->get('payment_easebuzz_status');

        $data['payment_easebuzz_sort_order'] = isset($this->request->post['payment_easebuzz_sort_order']) ?
            $this->request->post['payment_easebuzz_sort_order'] : $this->config->get('payment_easebuzz_sort_order');

        //Status
        $data['payment_easebuzz_new_status'] = isset($this->request->post['payment_easebuzz_new_status']) ?
            $this->request->post['payment_easebuzz_new_status'] : $this->config->get('payment_easebuzz_new_status');

        $data['payment_easebuzz_payment_mode'] = isset($this->request->post['payment_easebuzz_payment_mode']) ?
            $this->request->post['payment_easebuzz_payment_mode'] : $this->config->get('payment_easebuzz_payment_mode');

        $data['payment_easebuzz_merchant_salt'] = isset($this->request->post['payment_easebuzz_merchant_salt']) ?
            $this->request->post['payment_easebuzz_merchant_salt'] : $this->config->get('payment_easebuzz_merchant_salt');

        $data['payment_easebuzz_merchant_key'] = isset($this->request->post['payment_easebuzz_merchant_key']) ?
            $this->request->post['payment_easebuzz_merchant_key'] : $this->config->get('payment_easebuzz_merchant_key');

        $data['payment_easebuzz_cancelled_status'] = isset($this->request->post['payment_easebuzz_cancelled_status']) ?
            $this->request->post['payment_easebuzz_cancelled_status'] : $this->config->get('payment_easebuzz_cancelled_status');

        $data['payment_easebuzz_complete_status'] = isset($this->request->post['payment_easebuzz_complete_status']) ?
            $this->request->post['payment_easebuzz_complete_status'] : $this->config->get('payment_easebuzz_complete_status');

        //Breadcroumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/easebuzz', 'user_token=' . $this->session->data['user_token'], true)
        );

        //links
        $data['action'] = $this->url->link('extension/payment/easebuzz', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/easebuzz', $data));

    }

    //validate
    private function validate() {
        //permisions
        if (!$this->user->hasPermission('modify', 'extension/payment/easebuzz')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        //check for errors
        if (!$this->request->post['payment_easebuzz_merchant_key']) {
            var_dump($this->request->post);
            $this->error['merchant_key'] = $this->language->get('error_merchant_key');
        }
        if (!$this->request->post['payment_easebuzz_merchant_salt']) {
            var_dump($this->request->post);
            $this->error['merchant_salt'] = $this->language->get('error_merchant_salt');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('setting/setting');
        $this->settings = array(
            'payment_easebuzz_new_status' => 1,
            'payment_easebuzz_complete_status' => 5,
            'payment_easebuzz_cancelled_status' => 10,
            'payment_easebuzz_geo_zone_id' => 0,
            'payment_easebuzz_payment_mode' => 'test',
            'payment_easebuzz_sort_order' => 1,
        );
        $this->model_setting_setting->editSetting('payment_easebuzz', $this->settings);
    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('payment_easebuzz');
    }

}
