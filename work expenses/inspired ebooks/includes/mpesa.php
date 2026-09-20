<?php
require_once 'config/mpesa.php';

class MpesaAPI {
    private $access_token;
    
    public function __construct() {
        $this->access_token = $this->generateAccessToken();
    }
    
    private function generateAccessToken() {
        $url = MpesaConfig::getBaseUrl() . MpesaConfig::AUTH_URL;
        $credentials = base64_encode(MpesaConfig::CONSUMER_KEY . ':' . MpesaConfig::CONSUMER_SECRET);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['access_token'])) {
            throw new Exception('Failed to get access token: ' . $response);
        }
        
        return $result['access_token'];
    }
    
    public function initiateSTKPush($phone, $amount, $account_reference, $transaction_desc) {
        $url = MpesaConfig::getBaseUrl() . MpesaConfig::STK_PUSH_URL;
        $timestamp = date('YmdHis');
        $password = base64_encode(MpesaConfig::BUSINESS_SHORT_CODE . MpesaConfig::PASSKEY . $timestamp);
        
        // Format phone number (remove + and ensure it starts with 254)
        $phone = $this->formatPhoneNumber($phone);
        
        $data = [
            'BusinessShortCode' => MpesaConfig::BUSINESS_SHORT_CODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => MpesaConfig::BUSINESS_SHORT_CODE,
            'PhoneNumber' => $phone,
            'CallBackURL' => MpesaConfig::getCallbackUrl(),
            'AccountReference' => $account_reference,
            'TransactionDesc' => $transaction_desc
        ];
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        return json_decode($response, true);
    }
    
    public function querySTKStatus($checkout_request_id) {
        $url = MpesaConfig::getBaseUrl() . MpesaConfig::STK_QUERY_URL;
        $timestamp = date('YmdHis');
        $password = base64_encode(MpesaConfig::BUSINESS_SHORT_CODE . MpesaConfig::PASSKEY . $timestamp);
        
        $data = [
            'BusinessShortCode' => MpesaConfig::BUSINESS_SHORT_CODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkout_request_id
        ];
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        return json_decode($response, true);
    }
    
    private function formatPhoneNumber($phone) {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 254
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // If starts with +254, remove the +
        if (substr($phone, 0, 4) === '+254') {
            $phone = substr($phone, 1);
        }
        
        // If doesn't start with 254, add it
        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
}
?>