<?php

class ControllerExtensionPaymentCubepay extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/cubepay');
        $this->load->model('extension/payment/cubepay');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_cubepay', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

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
            'href' => $this->url->link('extension/payment/cubepay', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/cubepay', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $fiats = $this->model_extension_payment_cubepay->getFiat();
        usort($fiats['data'], array($this, 'usortTest'));
        $data['fiats'] = $fiats['data'];

        if (isset($this->request->post['payment_cubepay_url'])) {
            $data['payment_cubepay_url'] = $this->request->post['payment_cubepay_url'];
        } else {
            $data['payment_cubepay_url'] = $this->config->get('payment_cubepay_url');
        }

        if (isset($this->request->post['payment_cubepay_client_id'])) {
            $data['payment_cubepay_client_id'] = $this->request->post['payment_cubepay_client_id'];
        } else {
            $data['payment_cubepay_client_id'] = $this->config->get('payment_cubepay_client_id');
        }

        if (isset($this->request->post['payment_cubepay_client_secret'])) {
            $data['payment_cubepay_client_secret'] = $this->request->post['payment_cubepay_client_secret'];
        } else {
            $data['payment_cubepay_client_secret'] = $this->config->get('payment_cubepay_client_secret');
        }

        if (isset($this->request->post['payment_cubepay_fiat'])) {
            $data['payment_cubepay_fiat'] = $this->request->post['payment_cubepay_fiat'];
        } else {
            $data['payment_cubepay_fiat'] = $this->config->get('payment_cubepay_fiat');
        }

        if (isset($this->request->post['payment_cubepay_status'])) {
            $data['payment_cubepay_status'] = $this->request->post['payment_cubepay_status'];
        } else {
            $data['payment_cubepay_status'] = $this->config->get('payment_cubepay_status');
        }

        if (isset($this->request->post['payment_cubepay_sort_order'])) {
            $data['payment_cubepay_sort_order'] = $this->request->post['payment_cubepay_sort_order'];
        } else {
            $data['payment_cubepay_sort_order'] = $this->config->get('payment_cubepay_sort_order');
        }

        if (isset($this->request->post['payment_cubepay_currency'])) {
            $data['payment_cubepay_currency'] = $this->request->post['payment_cubepay_currency'];
        } else {
            $data['payment_cubepay_currency'] = $this->config->get('payment_cubepay_currency');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/payment/cubepay', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/cubepay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    private static function usortTest($a, $b)
    {
        return strnatcmp($a['name'], $b['name']);
    }
}