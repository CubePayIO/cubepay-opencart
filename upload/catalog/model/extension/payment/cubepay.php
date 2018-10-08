<?php

class ModelExtensionPaymentCubepay extends Model
{
    public $logFileName = 'cubepay_log';

    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/cubepay');
        $method_data = array(
            'code' => 'cubepay',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('payment_cubepay_sort_order')
        );
        return $method_data;
    }

    function getApiCall($params)
    {
        $api_url = (!empty($this->config->get('payment_cubepay_url'))) ? $this->config->get('payment_cubepay_url') : "https://api.cubepay.io";
        $api_url .= '/payment';
        $sign = $this->sign($params);
        $params['sign'] = $sign;

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        curl_close($ch);
        $log = new Log($this->logFileName);
        $log->write("response: " . var_export($result, true));
        return json_decode($result, true);
    }

    protected function sign($data)
    {
        $payment_cubepay_secret = $this->config->get('payment_cubepay_client_secret');
        ksort($data);
        $data_string = urldecode(http_build_query($data)) . "&client_secret=" . $payment_cubepay_secret;
        $sign = strtoupper(hash("sha256", $data_string));
        return $sign;
    }
}
