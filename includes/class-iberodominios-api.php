<?php
if (!defined('ABSPATH'))
    exit;

class Iberodominios_API
{
    private $api_base = 'https://api.openprovider.eu/v1beta/';
    private $username;
    private $password;
    private $token;

    public function __construct($username = '', $password = '', $token = '')
    {
        $this->username = $username ? $username : get_option('iberodominios_api_username');
        $this->password = $password ? $password : get_option('iberodominios_api_password');
        $this->token = $token ? $token : get_option('iberodominios_api_token');
    }

    public function generate_token()
    {
        $endpoint = $this->api_base . 'auth/login';
        $body = [
            'username' => $this->username,
            'password' => $this->password,
            'ip' => '0.0.0.0'
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($body),
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($data['data']['token'])) {
            return $data['data']['token'];
        }
        return false;
    }

    public function check_domain_availability($domain)
    {
        if (empty($this->token)) {
            return false;
        }

        $parts = explode('.', $domain);
        if (count($parts) < 2) {
            return false;
        }
        $name = array_shift($parts);
        $extension = implode('.', $parts);

        $endpoint = $this->api_base . 'domains/check';
        $body = [
            'domains' => [
                [
                    'extension' => $extension,
                    'name' => $name
                ]
            ],
            'with_price' => true
        ];

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ],
            'body' => json_encode($body),
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($data && isset($data['data']['results']) && isset($data['data']['results'][0])) {
            return $data['data']['results'][0];
        }
        //error_log("Respuesta API check_domain_availability: " . print_r($data, true));
        //error_log("Llamando a la API para $domain");
        //error_log("Request body: " . print_r($body, true)); // Antes de enviar
        //error_log("Response: " . print_r($response, true));

        return false;
    }

    public function get_tld($name)
    {
        if (empty($this->token)) {
            return false;
        }

        $endpoint = $this->api_base . 'tlds/' . strtolower($name);

        $response = wp_remote_get($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token
            ],
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($data['data']['name'])) {
            return strtolower($data['data']['name']);
        }

        return false;
    }
}
