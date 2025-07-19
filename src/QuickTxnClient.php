<?php

namespace QuickTxn;

use GuzzleHttp\Client;

class QuickTxnClient
{
    private string $userToken;
    private Client $http;

    public function __construct(string $userToken)
    {
        $this->userToken = $userToken;
        $this->http = new Client([
            'base_uri' => 'https://manage.quicktxn.in/api/',
            'timeout'  => 10.0,
        ]);
    }

    public function createOrder(array $params): array
    {
        $required = ['customer_mobile', 'amount', 'order_id', 'redirect_url'];
        foreach ($required as $r) {
            if (empty($params[$r])) {
                throw new \InvalidArgumentException("Missing parameter: {$r}");
            }
        }

        $response = $this->http->post('create-order', [
            'form_params' => array_merge($params, [
                'user_token' => $this->userToken
            ])
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    public function checkOrderStatus(string $orderId): array
    {
        $response = $this->http->post('check-order-status', [
            'form_params' => [
                'user_token' => $this->userToken,
                'order_id'   => $orderId,
            ]
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    public function verifyWebhook(array $payload): bool
    {
        if (empty($payload['order_id'] ?? null) || empty($payload['status'] ?? null)) {
            return false;
        }
        return true;
    }
}
