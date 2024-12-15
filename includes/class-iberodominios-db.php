<?php
if (!defined('ABSPATH'))
    exit;

class Iberodominios_DB
{
    public static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'iberodominios_tlds';
    }

    public static function tld_exists($tld)
    {
        global $wpdb;
        $table = self::get_table_name();
        $res = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE tld_name = %s", $tld));
        return $res ? true : false;
    }

    public static function insert_tld($tld_name, $status = 'ACT')
    {
        global $wpdb;
        $table = self::get_table_name();
        $wpdb->insert($table, ['tld_name' => strtolower($tld_name), 'status' => $status]);
    }

    public static function get_popular_tlds()
    {
        $popular = (array) get_option('iberodominios_popular_tlds', []);
        return $popular;
    }

    public static function get_tlds_batch($offset = 0, $limit = 10, $exclude = array())
    {
        global $wpdb;
        $table = self::get_table_name();
        if (!empty($exclude)) {
            $placeholders = implode(',', array_fill(0, count($exclude), '%s'));
            $sql = $wpdb->prepare(
                "SELECT tld_name FROM $table WHERE tld_name NOT IN ($placeholders) ORDER BY tld_name ASC LIMIT %d OFFSET %d",
                array_merge($exclude, [$limit, $offset])
            );
        } else {
            $sql = $wpdb->prepare("SELECT tld_name FROM $table ORDER BY tld_name ASC LIMIT %d OFFSET %d", $limit, $offset);
        }
        return $wpdb->get_col($sql);
    }

    public static function search_tlds($query, $limit = 50)
    {
        global $wpdb;
        $table = self::get_table_name();
        $like = '%' . $wpdb->esc_like($query) . '%';
        $sql = $wpdb->prepare("SELECT tld_name FROM $table WHERE tld_name LIKE %s ORDER BY tld_name ASC LIMIT %d", $like, $limit);
        return $wpdb->get_col($sql);
    }
}
