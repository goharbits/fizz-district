<?php

namespace App\Services\V1;

use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;


class CloverService
{
    protected $apiBaseUrl;
    protected $apiKey;
    protected $merchantId;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.clover.base_url');
        $this->rewardUpBaseUrl = config('services.rewardup.base_url');
        $this->rewardToken = config('services.rewardup.key');
        $this->apiKey = config('services.clover.api_key');
        $this->merchantId = config('services.clover.merchant_id');
        $this->ecommPubKey = config('services.clover.ecommerce_public_api_key');
        $this->ecommPrivateKey = config('services.clover.ecommerce_private_api_key');
    }

    protected function makeRequest($method, $endpoint, $params = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->{$method}("{$this->apiBaseUrl}{$endpoint}", $params);
            Log::Info('endpoint '. $endpoint);
            return $this->formatCloverResponse($response);
        } catch (\Exception $e) {

            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }
    }


    protected function makeRewardRequest($method, $endpoint, $params = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->rewardToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->{$method}("{$this->rewardUpBaseUrl}{$endpoint}", $params);
            Log::Info('endpoint RewardUp '. $endpoint);
            return $this->formatRewardResponse($response);
        } catch (\Exception $e) {
            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }
    }

        protected function formatRewardResponse($response)
    {

        if ($response->status() == 201) {
            $jsonResponse = $response->json();
            Log::Info(["Success Response" => json_encode($jsonResponse)]);
            return ResponseHelper::formatResponse(true, $jsonResponse, "Success");
        } else {
            $jsonResponse = $response->json();

            // Check if "message" is an array and retrieve the first message if available
            $errorMessage = "Unexpected error occurred.";
            if (is_array($jsonResponse) && isset($jsonResponse['message'])) {
                if (is_array($jsonResponse['message']) && isset($jsonResponse['message'][0])) {
                    $errorMessage = $jsonResponse['message'][0];
                } else {
                    $errorMessage = $jsonResponse['message'];
                }
            } elseif (isset($jsonResponse['error'])) {
                $errorMessage = $jsonResponse['error'];
            }
            Log::Error(["Error Response RewardUp" => "Error: " . $response->status() . ' ' . $errorMessage]);
            return ResponseHelper::formatResponse(false, [],  $errorMessage);
        }
    }



    public function createCategoryOnClover($categoryData)
    {
           try{

            $endpoint = "/merchants/{$this->merchantId}/categories";

            $payload = [
                'name' => $categoryData['name'],
            ];

            // Pass the payload directly for a JSON request
            return $this->makeRequest('post', $endpoint, $payload);

           } catch (\Exception $e) {
            //   dd($ex->getMessage());
           }


    }

    protected function formatCloverResponse($response)
    {

        if ($response->status() == 200) {
            Log::info(["Success Response" => $response->json()]);
            return ResponseHelper::formatResponse(true, $response->json(), "Success");
        } else {
            $res =  $response->json();

            Log::info(["Error Response" => "Error: " . @$response->status() .' '. @$res['error']['message']]);
            return ResponseHelper::formatResponse(false, [],  @$res['error']['message'] );
        }
    }

    public function registerCustomerOnClover($customerData)
    {
           try{

            $endpoint = "/merchants/{$this->merchantId}/customers";
            $payload = [
                'firstName' => $customerData['firstName'],
                'lastName' => $customerData['lastName'] ?? '',
                'marketingAllowed' => false,
                'phoneNumbers' => [['phoneNumber' => $customerData['phoneNumber']]],
            ];

            // if (!empty($customerData['email'])) {
            //     $payload['emailAddresses'] = [['emailAddresses' => 'tester@gmial.com']];
            // }

            // Pass the payload directly for a JSON request
            return $this->makeRequest('post', $endpoint, $payload);

           } catch (\Exception $e) {
            //   dd($ex->getMessage());
           }

    }


     public function registerCustomerOnRewardUp($customerData)
    {
           try{

            $endpoint = "/api/v1/members";

            $payload = [
                'name' => $customerData['firstName'],
                'phone_number' => $customerData['phoneNumber'] ,
                "can_send_sms" => false,
                "can_send_sms" => false,
            ];

            if (!empty($customerData['email'])) {
                $payload['email'] = $customerData['email'];
            }

            Log::Info('Payload Reward up Create Member .'. json_encode($payload));

            // Pass the payload directly for a JSON request

            return $this->makeRewardRequest('post', $endpoint, $payload);

           } catch (\Exception $e) {
            //   dd($ex->getMessage());
           }

    }

    public function validateCloverItemId($cloverId)
    {
        $endpoint = "/merchants/{$this->merchantId}/items/{$cloverId}";
        return $this->makeRequest('get', $endpoint);
    }

    public function createCloverItem($itemName, $itemPrice = 0)
    {
        $endpoint = "/merchants/{$this->merchantId}/items";
        $payload = [
            'name' => $itemName,
            'price' => $itemPrice,
        ];

        return $this->makeRequest('post', $endpoint, $payload);
    }

    public function createOrder($orderData)
    {
        $endpoint = "/merchants/{$this->merchantId}/orders";
        return $this->makeRequest('post', $endpoint, $orderData);
    }

    // New method to create an order
    public function createAtomicOrder($orderData)
    {
        $endpoint = "/merchants/{$this->merchantId}/atomic_order/orders";
        return $this->makeRequest('post', $endpoint, $orderData);
    }

    public function createPayment($cloverOrderId, $paymentData)
    {
        $endpoint = "/merchants/{$this->merchantId}/orders/{$cloverOrderId}/payments";
        return $this->makeRequest('post', $endpoint, $paymentData);
    }


    public function addItemInModifier($request)
    {
        $endpoint = "/merchants/{$this->merchantId}/item_modifier_groups";
        $payload = [
                        'modifierGroup' => [
                            'id' => '7YRCEA7R0Z5AR'
                        ],
                        'item' => [
                            'id' => '8XX3GMX1JQG0W'
                        ]
                    ];
        return $this->makeRequest('post', $endpoint, $payload);
    }

     public function getCustomers(){
        $endpoint = "/merchants/{$this->merchantId}/customers";
        return $this->makeRequest('get', $endpoint);
    }

    public function getCategories(){
        $endpoint = "/merchants/{$this->merchantId}/categories";
        return $this->makeRequest('get', $endpoint);
    }

    public function getAllItemsAgainstCategory($cat_id){
        $endpoint = "/merchants/{$this->merchantId}/categories/$cat_id/items";
        return $this->makeRequest('get', $endpoint,);
    }

    public function getItems(){
        $endpoint = "/merchants/{$this->merchantId}/items";
        return $this->makeRequest('get', $endpoint);
    }

    public function getModifierGroups(){
        $endpoint = "/merchants/{$this->merchantId}/modifier_groups";
        return $this->makeRequest('get', $endpoint);
    }

    public function getSingleModifierGroups($mod_id){
        $endpoint = "/merchants/{$this->merchantId}/modifier_groups";
        return $this->makeRequest('get', $endpoint);
    }

    public function getModifiers($mod_id){
        $endpoint = "/merchants/{$this->merchantId}/modifier_groups/$mod_id/modifiers";
        return $this->makeRequest('get', $endpoint);
    }

    public function getItemsAgainstModifierGroups($mod_group_id){
        $endpoint = "/merchants/{$this->merchantId}/modifier_groups/$mod_group_id/items";
        return $this->makeRequest('get', $endpoint);
    }

    public function createTender(){
        $endpoint = "/merchants/{$this->merchantId}/tenders";
        return $this->makeRequest('post', $endpoint);
    }


    public function deleteRewardUpMember($reward_up_id){

        $url = $this->rewardUpBaseUrl."/api/v1/members/{$reward_up_id}";

        $headers = [
            'authorization' => 'Bearer ' . $this->rewardToken,
        ];

        $response = Http::withHeaders($headers)->delete($url);

        if ($response->successful()) {

        // If the API returns an empty response {}, handle it as a success
            if (empty($response->json())) {
                return response()->json([
                    'message' => 'Member deleted  from rewards.'
                ], 200);
            }
        }

        return response()->json([
            'message' => $response->json('message') ?? 'Failed to delete member'
        ], $response->status());

    }


     public function delelteCustomerAccount($customerId)
    {
        $url = $this->apiBaseUrl."/merchants/{$this->merchantId}/customers/{$customerId}";
        $headers = [
            'authorization' => 'Bearer ' . $this->apiKey,
        ];

        $response = Http::withHeaders($headers)->delete($url);

        if ($response->successful()) {

        // If the API returns an empty response {}, handle it as a success
            if (empty($response->json())) {
                return response()->json([
                    'message' => 'Customer deleted successfully.'
                ], 200);
            }
        }

        return response()->json([
            'message' => $response->json('message') ?? 'Failed to delete customer'
        ], $response->status());

    }

    public function createPromo($request)
    {

        $url = "https://api.clover.com/v3/merchants/{$this->merchantId}/promos";

        $headers = [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $this->apiKey,
            'content-type' => 'application/json',
        ];

        $body = [
            "name" => "First-Time Free Order",
            "discountType" => "PERCENTAGE",
            "percentage" => 100,
            "description" => "First-time users get their first order free.",
            "status" => "ACTIVE",
            "maxRedemptions" => 1,
            "customerType" => "NEW_CUSTOMER"
        ];

        $response = Http::withHeaders($headers)->post($url, $body);

        if ($response->successful()) {
          $a =  $response->json();

        } else {
            // Handle error
            $e = $response->body();

            return ['error' => $response->body()];
        }


        $endpoint = "/merchants/{$this->merchantId}/promos";
        $payload = [
            "name" => "First-Time Free Order",
            "discountType" => "PERCENTAGE",
            "percentage" => 100,
            "description" => "First-time users get their first order free.",
            "status" => "ACTIVE",
            "maxRedemptions" => 1,
            "customerType" => "NEW_CUSTOMER"
        ];

        return $this->makeRequest('post', $endpoint, $payload);
    }


    public function createPaymentToken($request){
        try {
                $payload = [
                    'card' => [
                        'brand'  =>  $request['Brand'],
                        'number' =>  $request['CreditCardNumber'],
                        'exp_month' => $request['ExpMonth'],
                        'exp_year'  => $request['ExpYear'],
                        'cvv'    => $request['CVV'],
                        'last4'  => $request['Last4'],
                        'first6' => $request['First6'],
                        'address_zip' => $request['AddressZip'] ?? null
                    ],
                    // 'source' => 'multi_pay_token'
                ];

               if(env('APP_ENVIRONMENT') == 'staging')
                {
                    $url = 'https://token-sandbox.dev.clover.com/v1/tokens';
                    $key = 'bd83361519dc85008c21a5a137eadf6c';

                }else{
                    $url =  'https://token.clover.com/v1/tokens';
                    $key = $this->ecommPubKey;
                }

            // Log::Info('Payload Token '.json_encode($payload));

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'apikey' => $key,
                'content-type' => 'application/json',
            ])->post($url, $payload);

            Log::Info(['Payload Token response' => $response]);

            return $this->formatCloverResponse($response);
        } catch (\Exception $e) {
            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }

    }


    public function createPaymentAppleToken($request){
        try {
                $payload = [
                  'encryptedWallet' => [
                        'applePayPaymentData' => [
                            'header' => [
                                'publicKeyHash'    => $request['PublicKeyHash'],
                                'transactionId'    => $request['TransactionId'],
                                'ephemeralPublicKey' => $request['EphemeralPublicKey'],
                            ],
                            'data'      => $request['Data'],
                            'signature' => $request['Signature'],
                            'version'   => $request['Version'],
                        ],
                        'address_line1' => $request['address_line1'],
                        'address_zip'   => $request['address_zip'],
                    ],
                ];
                Log::Info('Payload apple pay'. json_encode($payload));
               if(env('APP_ENVIRONMENT') == 'staging')
                {
                    $url = 'https://token-sandbox.dev.clover.com/v1/tokens';
                    $key = 'bd83361519dc85008c21a5a137eadf6c';

                }else{
                    $url =  'https://token.clover.com/v1/tokens';
                    $key = $this->ecommPubKey;
                }

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'apikey' => $key,
                'content-type' => 'application/json',
            ])->post($url, $payload);

            Log::info(['response apple token' => $response]);

            return $this->formatCloverResponse($response);
        } catch (\Exception $e) {
            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }

    }

    public function deleteCustomerCard(){
        try {
                $user = Auth::user();
                 if(env('APP_ENVIRONMENT') == 'staging')
                {
                    $url = 'https://scl-sandbox.dev.clover.com/v1/customers/'.$user->clover_id.'/sources/'.$user->clover_pay_id;
                    $key = '2cfe6864-5975-c55d-0f69-b035ac02bc5f';
                }else{
                    $url = 'https://scl.clover.com/v1/customers/'.$user->clover_id.'/sources/'.$user->clover_pay_id;
                    $key =   $this->ecommPrivateKey;
                }
            $response = Http::withHeaders([
                'authorization' => 'Bearer '.$key,
            ])->delete($url);

            Log::info(['response' => $response]);

            return $this->formatCloverResponse($response);
        } catch (\Exception $e) {
            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }

    }


     public function assignCardToCustomer($request){
        try {
                $user_clover_id = Auth::user()->clover_id;
                $email = Auth::user()->email;

                // $user_clover_id = 'EATTK51DSP8CJ';

                $payload = [
                    'source' => $request['token_id'],
                    'email'=> config('app.cardChangeEmail'),
                ];

                if(env('APP_ENVIRONMENT') == 'staging')
                {
                    $url = 'https://scl-sandbox.dev.clover.com/v1/customers/'.$user_clover_id;
                    $key = '2cfe6864-5975-c55d-0f69-b035ac02bc5f';
                }else{
                    $url =  'https://scl.clover.com/v1/customers/'.$user_clover_id;
                    $key =   $this->ecommPrivateKey;
                }

                $response = Http::withHeaders([
                    // 'accept' => 'application/json',
                    'authorization' => 'Bearer '. $key,
                    // 'content-type' => 'application/json',
                ])->put($url, $payload);

                Log::info(['response' => $response]);

            return $this->formatCloverResponse($response);
        } catch (\Exception $e) {
            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }

    }

    public function detachOldCard($request){
        try{

            $user_clover_id = Auth::user()->clover_id;
            $clover_pay_id = Auth::user()->clover_pay_id;

             if(env('APP_ENVIRONMENT') == 'staging')
            {
                $url = 'https://scl-sandbox.dev.clover.com/v1/customers/'.$user_clover_id.'/sources/'.$clover_pay_id;
                $key = '2cfe6864-5975-c55d-0f69-b035ac02bc5f';
            }else{
                $url = 'https://scl.clover.com/v1/customers/'.$user_clover_id.'/sources/'.$clover_pay_id;
                $key = $this->ecommPrivateKey;
            }

            $response = Http::withHeaders([
                'authorization' => 'Bearer '.$key,
            ])->delete($url);

                Log::info(['response' => $response]);

                if ($response->status() != 200) {
                    $errorMessage = $response->json()['error']['message'] ?? "Unknown error: " . $response->status();
                    Log::error(['Error' => $errorMessage]);
                    // $errorMessage = 'There was an error processing your payment. Please contact your bank or try again.';
                    return ResponseHelper::formatResponse(false, [], $errorMessage);
                }
            return  $this->formatCloverResponse($response);

        } catch (\Exception $e) {
                Log::error(['Error' => $e->getMessage()]);
            $errorMessage = $e->getMessage();
            return ResponseHelper::formatResponse(false, [], $errorMessage);
        }

    }

    public function createPaymentCharge($request){
         try {
               $user_clover_id = Auth::user()->clover_id;

               if(env('APP_ENVIRONMENT') == 'staging')
                {
                    $url = 'https://scl-sandbox.dev.clover.com/v1/charges';
                    $key = '2cfe6864-5975-c55d-0f69-b035ac02bc5f';
                }else{
                    $url = 'https://scl.clover.com/v1/charges';
                    $key = $this->ecommPrivateKey;
                }

                $payload = [
                    'ecomind' => 'ecom',
                    'metadata' => [
                        'existingDebtIndicator' => false,
                    ],
                    "source"=>[],
                    'amount' => $request['amount'] * 100,
                    'currency' => $request['currency'],
                    'source' => $request['token_id'] ,
                    "capture"=> true,
                    // ?? $user_clover_id,
                ];
                Log::info(['payload create Payment charge' => json_encode($payload)]);

                $response = Http::withHeaders([
                    'accept' => 'application/json',
                    'authorization' => 'Bearer '.$key,
                    'content-type' => 'application/json',
                ])->post($url, $payload);

                Log::info(['Response Payment Charge' => $response]);

                if ($response->status() != 200) {
                    $errorMessage = $response->json()['error']['message'] ?? "Unknown error: " . $response->status();
                    Log::error(['Error' => $errorMessage]);
                    // $errorMessage = 'There was an error processing your payment. Please contact your bank or try again.';
                    return ResponseHelper::formatResponse(false, [], $errorMessage);
                }
            return  $this->formatCloverResponse($response);
        } catch (\Exception $e) {
                Log::error(['Error' => $e->getMessage()]);
            // $errorMessage = 'There was an error processing your payment. Please contact your bank or try again.';
            return ResponseHelper::formatResponse(false, [], $errorMessage);
        }

    }


    public function getTender(){
        $endpoint = "/merchants/{$this->merchantId}/tenders";
        return $this->makeRequest('get', $endpoint);
    }


     public function printOrder($orderId){
        try {

            $url = "{$this->apiBaseUrl}/merchants/{$this->merchantId}/print_event";

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => 'Bearer '. $this->ecommPrivateKey,
                'content-type' => 'application/json',
            ])->post($url, [
                'orderRef' => [
                    'id' => $orderId,
                ],
            ]);

            if ($response->successful()) {
                $res =  $response->json(); // Return or process response data
                return ResponseHelper::formatResponse(true, null, 'Order Printed Successfully!');

            } else {
                // Handle error response
                $res =  $response->body();
                $resArray = json_decode($res, true);
                return ResponseHelper::formatResponse(false, [], $resArray['message']);

            }

            } catch (\Exception $e) {
            return ResponseHelper::formatResponse(false, [], $e->getMessage());
        }
    }

}
