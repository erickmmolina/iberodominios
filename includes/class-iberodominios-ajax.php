<?php
if (!defined('ABSPATH')) {
    exit;
}

class Iberodominios_AJAX
{
    public static function check_domain()
    {
        check_ajax_referer('iberodominios_ajax_nonce', 'security');

        $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
        if (empty($domain)) {
            wp_send_json_error(['message' => __('El dominio no puede estar vacío', 'iberodominios')]);
        }

        $api = new Iberodominios_API();
        $parts = explode('.', $domain);
        $initial_count = (int) get_option('iberodominios_initial_results_count', 100);
        $popular = Iberodominios_DB::get_popular_tlds();

        // Número máximo total de dominios a mostrar
        $max_count = 100;

        if (count($parts) == 1) {
            // Sin extensión
            $name = $parts[0];
            // Asegurar que tenemos al menos initial_count en populares
            if (count($popular) < $initial_count) {
                $need = $initial_count - count($popular);
                if ($need > 0) {
                    $extra = Iberodominios_DB::get_tlds_batch(0, $need, $popular);
                    $popular = array_merge($popular, $extra);
                }
            }

            $suggestions = $popular;

            // Si no llenamos el cupo, agregamos fallback
            if (count($suggestions) < $max_count) {
                $fneed = $max_count - count($suggestions);
                $fallback = Iberodominios_DB::get_tlds_batch(0, $fneed, $suggestions);
                $suggestions = array_merge($suggestions, $fallback);
            }

            // Construir results: solo 'domain'
            $results = [];
            foreach ($suggestions as $t) {
                $results[] = ['domain' => $name . '.' . $t];
            }

            wp_send_json_success([
                'mode' => 'list',
                'results' => $results
            ]);

        } else {
            // Con extensión
            $ext = strtolower(end($parts));
            $name = implode('.', array_slice($parts, 0, -1));

            if (!Iberodominios_DB::tld_exists($ext)) {
                $single_tld = $api->get_tld($ext);
                if (!$single_tld) {
                    wp_send_json_error([
                        'message' => __('Invalid extension', 'iberodominios'),
                        'mode' => 'invalid_extension'
                    ]);
                } else {
                    Iberodominios_DB::insert_tld($ext);
                }
            }

            $res = $api->check_domain_availability($domain);
            //error_log("API response for $domain: " . print_r($res, true));

            // Obtener sugerencias
            $all_popular = $popular;
            if (empty($all_popular)) {
                $all_popular = Iberodominios_DB::get_tlds_batch(0, $initial_count, [$ext]);
            }

            $all_popular = array_diff($all_popular, [$ext]);
            $all_popular = array_slice($all_popular, 0, $initial_count);
            $all_popular = array_filter($all_popular);

            $suggestions = $all_popular;
            // Si no llegamos a $max_count, agregamos fallback
            if (count($suggestions) < $max_count) {
                $fneed = $max_count - count($suggestions);
                $fallback = Iberodominios_DB::get_tlds_batch(0, $fneed, $suggestions);
                $fallback = array_filter($fallback);
                $final_tlds = array_values(array_unique(array_merge($suggestions, $fallback)));
            } else {
                $final_tlds = $suggestions;
            }

            $results = [];
            foreach ($final_tlds as $t) {
                $results[] = ['domain' => $name . '.' . $t];
            }

            // Dependiendo del estado del dominio exacto
            if ($res && isset($res['status'])) {
                if ($res['status'] === 'free') {
                    wp_send_json_success([
                        'mode' => 'exact',
                        'status' => 'available',
                        'domain' => $res['domain'],
                        'price' => $res['price']['reseller']['price'] ?? null,
                        'currency' => $res['price']['reseller']['currency'] ?? 'USD',
                        'suggestions' => $results
                    ]);
                } else {
                    wp_send_json_success([
                        'mode' => 'exact',
                        'status' => 'unavailable',
                        'domain' => $domain,
                        'suggestions' => $results
                    ]);
                }
            } else {
                wp_send_json_success([
                    'mode' => 'exact',
                    'status' => 'unavailable',
                    'domain' => $domain,
                    'suggestions' => $results
                ]);
            }
        }
    }

    public static function check_batch()
    {
        check_ajax_referer('iberodominios_ajax_nonce', 'security');

        $domains = isset($_POST['domains']) ? (array) $_POST['domains'] : [];
        if (empty($domains)) {
            wp_send_json_error(['message' => __('No domains provided.', 'iberodominios')]);
        }

        $api = new Iberodominios_API();
        $results = [];
        foreach ($domains as $dom) {
            $res = $api->check_domain_availability($dom);

            if ($res && isset($res['status'])) {
                if ($res['status'] === 'free') {
                    $price = $res['price']['reseller']['price'] ?? null;
                    $currency = $res['price']['reseller']['currency'] ?? 'USD';
                    $results[] = [
                        'domain' => $res['domain'],
                        'status' => 'free',
                        'price' => $price,
                        'currency' => $currency
                    ];
                } else {
                    $results[] = [
                        'domain' => $dom,
                        'status' => $res['status']
                    ];
                }
            } else {
                $results[] = [
                    'domain' => $dom,
                    'status' => 'unavailable'
                ];
            }
        }

        wp_send_json_success($results);
    }
}
