<?php
if (!defined('ABSPATH'))
    exit;

class Iberodominios_AJAX
{
    public static function check_domain()
    {
        check_ajax_referer('iberodominios_ajax_nonce', 'security');

        $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
        $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
        $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 100;

        if (empty($domain)) {
            wp_send_json_error(['message' => __('El dominio no puede estar vacío', 'iberodominios')]);
        }

        $api = new Iberodominios_API();
        $parts = explode('.', $domain);
        $initial_count = (int) get_option('iberodominios_initial_results_count', 100);
        $popular = Iberodominios_DB::get_popular_tlds();

        // Número máximo total (teórico) para ejemplo, puedes quitarlo o ajustarlo
        $max_count = 2000; // Ejemplo: ahora permitimos hasta 2000 si hay

        if (count($parts) == 1) {
            // Sin extensión
            $name = $parts[0];
            // Asegurar que tenemos al menos initial_count
            if (count($popular) < $initial_count) {
                $need = $initial_count - count($popular);
                if ($need > 0) {
                    $extra = Iberodominios_DB::get_tlds_batch(0, $need, $popular);
                    $popular = array_merge($popular, $extra);
                }
            }

            $suggestions = $popular;

            // Si no llenamos el cupo, agregamos fallback
            $total_suggestions = count($suggestions);
            if ($total_suggestions < $max_count) {
                $fneed = $max_count - $total_suggestions;
                $fallback = Iberodominios_DB::get_tlds_batch(0, $fneed, $suggestions);
                $suggestions = array_merge($suggestions, $fallback);
            }

            // Ahora aplicamos offset y limit
            $paged_suggestions = array_slice($suggestions, $offset, $limit);
            $has_more = (count($suggestions) > $offset + $limit);

            $results = [];
            foreach ($paged_suggestions as $t) {
                $results[] = ['domain' => $name . '.' . $t];
            }

            wp_send_json_success([
                'mode' => 'list',
                'results' => $results,
                'has_more' => $has_more
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

            $all_popular = $popular;
            if (empty($all_popular)) {
                $all_popular = Iberodominios_DB::get_tlds_batch(0, $initial_count, [$ext]);
            }

            $all_popular = array_diff($all_popular, [$ext]);
            $all_popular = array_slice($all_popular, 0, $max_count);
            $all_popular = array_filter($all_popular);

            // Paginamos las sugerencias también
            $paged_suggestions = array_slice($all_popular, $offset, $limit);
            $has_more = (count($all_popular) > $offset + $limit);

            $results = [];
            foreach ($paged_suggestions as $t) {
                $results[] = ['domain' => $name . '.' . $t];
            }

            if ($res && isset($res['status'])) {
                if ($res['status'] === 'free') {
                    wp_send_json_success([
                        'mode' => 'exact',
                        'status' => 'available',
                        'domain' => $res['domain'],
                        'price' => $res['price']['reseller']['price'] ?? null,
                        'currency' => $res['price']['reseller']['currency'] ?? 'USD',
                        'suggestions' => $results,
                        'has_more' => $has_more
                    ]);
                } else {
                    wp_send_json_success([
                        'mode' => 'exact',
                        'status' => 'unavailable',
                        'domain' => $domain,
                        'suggestions' => $results,
                        'has_more' => $has_more
                    ]);
                }
            } else {
                wp_send_json_success([
                    'mode' => 'exact',
                    'status' => 'unavailable',
                    'domain' => $domain,
                    'suggestions' => $results,
                    'has_more' => $has_more
                ]);
            }
        }
    }

    public static function check_single()
    {
        check_ajax_referer('iberodominios_ajax_nonce', 'security');

        $dom = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
        if (empty($dom)) {
            wp_send_json_error(['message' => __('No domain provided.', 'iberodominios')]);
        }

        $api = new Iberodominios_API();
        $res = $api->check_domain_availability($dom);

        if ($res && isset($res['status'])) {
            if ($res['status'] === 'free') {
                $price = $res['price']['reseller']['price'] ?? null;
                $currency = $res['price']['reseller']['currency'] ?? 'USD';
                wp_send_json_success([
                    'domain' => $res['domain'],
                    'status' => 'free',
                    'price' => $price,
                    'currency' => $currency
                ]);
            } else {
                wp_send_json_success([
                    'domain' => $dom,
                    'status' => $res['status']
                ]);
            }
        } else {
            wp_send_json_success([
                'domain' => $dom,
                'status' => 'unavailable'
            ]);
        }
    }
}
