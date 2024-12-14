<?php
/**
 * Clase para interactuar con la API de Openprovider
 * 
 * Esta clase se encarga de:
 * - Generar el token de autenticación
 * - Consultar disponibilidad de dominios
 * 
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Iberodominios_API {
    // URL base de la API
    private $api_base = 'https://api.openprovider.eu/v1beta/';
    private $username;
    private $password;
    private $token;

    /**
     * Constructor
     * @param string $username Usuario de la API
     * @param string $password Contraseña de la API
     * @param string $token Token (opcional, si ya se tiene guardado)
     */
    public function __construct( $username = '', $password = '', $token = '' ) {
        $this->username = $username ? $username : get_option('iberodominios_api_username');
        $this->password = $password ? $password : get_option('iberodominios_api_password');
        $this->token    = $token ? $token : get_option('iberodominios_api_token');
    }

    /**
     * Generar token de acceso solicitándolo a la API
     * 
     * @return string|bool token o false en caso de error
     */
    public function generate_token() {
        // Endpoint para generar token
        $endpoint = $this->api_base . 'auth/login';

        // Datos de la petición
        $body = array(
            'username' => $this->username,
            'password' => $this->password,
            'ip'       => '0.0.0.0' // Requerido por la API, puede cambiarse
        );

        // Petición POST
        $response = wp_remote_post( $endpoint, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => json_encode( $body ),
            'timeout' => 20,
        ));

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset($data['data']['token']) ) {
            return $data['data']['token'];
        }

        return false;
    }

    /**
     * Consultar disponibilidad de un dominio
     * 
     * @param string $domain El nombre del dominio (ejemplo: "ejemplo.com")
     * @return array|bool Array con la respuesta o false si hay error
     */
    public function check_domain_availability( $domain ) {
        // Verificamos que tengamos token
        if ( empty($this->token) ) {
            return false;
        }

        // Separar dominio y extensión
        $parts = explode('.', $domain);
        if ( count($parts) < 2 ) {
            return false;
        }
        $name = $parts[0];
        $extension = implode('.', array_slice($parts, 1));

        $endpoint = $this->api_base . 'domains/check';
        $body = array(
            'domains' => array(
                array(
                    'extension' => $extension,
                    'name'      => $name
                )
            ),
            'with_price' => true
        );

        $response = wp_remote_post( $endpoint, array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ),
            'body'    => json_encode($body),
            'timeout' => 20,
        ));

        if ( is_wp_error($response) ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body($response), true );
        if ( $data && isset($data['data']['results']) ) {
            return $data['data']['results'][0];
        }

        return false;
    }
}
