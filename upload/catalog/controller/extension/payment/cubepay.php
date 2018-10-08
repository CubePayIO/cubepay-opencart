<?php

class ControllerExtensionPaymentCubepay extends Controller
{
    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $this->load->model('extension/payment/cubepay');
        $data['action'] = "index.php?route=extension/payment/cubepay/confirm";
        return $this->load->view('extension/payment/cubepay', $data);
    }

    private function getGoods()
    {
        $goods = [];
        $products = $this->cart->getProducts();
        foreach ($products as $product) {
            $goods[] = $product['name'] . ' x ' . $product['quantity'];
        }
        return implode(",", $goods);
    }

    public function confirm()
    {
        if ($this->session->data['payment_method']['code'] == 'cubepay') {
            $this->load->model('extension/payment/cubepay');
            $this->load->model('checkout/order');
            $token = md5(time() . mt_rand(0, 1000));
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $order_info["payment_custom_field"] = array('token' => $token);
            $this->model_checkout_order->editOrder($this->session->data['order_id'], $order_info);
            $out_trade_no = trim($order_info['order_id']);
            $total_amount = trim($this->currency->format($order_info['total'], 'NTD', '', false));
            $payRequestBuilder = array(
                'client_id' => $this->config->get('payment_cubepay_client_id'),
                'source_coin_id' => $this->config->get('payment_cubepay_fiat'),
                'source_amount' => $total_amount,
                'item_name' => $this->getGoods(),
                'merchant_transaction_id' => $out_trade_no,
                'return_url' => '',
                'ipn_url' => HTTPS_SERVER . "/index.php?route=extension/payment/cubepay/callback",
                'other' => $token
            );
            $response = $this->model_extension_payment_cubepay->getApiCall($payRequestBuilder);

            if ($response['status'] == 200 && !empty($response["data"])) {
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);
                if (isset($this->session->data['order_id'])) {
                    $this->cart->clear();
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['guest']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['order_id']);
                    unset($this->session->data['coupon']);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data['vouchers']);
                    unset($this->session->data['totals']);
                }
                $this->response->redirect($response["data"]);
            } else {
                $this->response->redirect($this->url->link('checkout/failure'));
            }
        }
    }

    public function callback()
    {
        $merchant_transaction_id = $_POST['merchant_transaction_id'];
        $token = $_POST['other'];
        $this->load->model('checkout/order');
        $this->load->language('extension/payment/cubepay');
        $order_info = $this->model_checkout_order->getOrder($merchant_transaction_id);
        if ($order_info["payment_custom_field"]["token"] && $order_info["payment_custom_field"]["token"] == trim($token)) {
            $this->model_checkout_order->addOrderHistory($merchant_transaction_id, 2, $this->language->get('user_paid'));
        } else {
            error_log('paid error' . $merchant_transaction_id);
        }
        echo "success";
    }
}