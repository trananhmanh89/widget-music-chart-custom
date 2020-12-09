<?php 
defined('WPINC') or die('error');

class MusicChartCustomHelper
{
    public static function getChartCustomData($settings)
    {
        if (empty($settings['chart_id'])) {
            return array();
        }

        $wpdb = ff_get_db();
        $chart = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ff_charts WHERE id = " . (int) $settings['chart_id']);

        switch ($chart->type) {
            case 'album':
                $sql = "SELECT a.*, b.*, c.title AS artist 
                    FROM {$wpdb->prefix}ff_chart_data a
                    INNER JOIN {$wpdb->prefix}ff_albums b ON b.id = a.item_id
                    INNER JOIN {$wpdb->prefix}ff_artists c ON c.id = b.artist
                    WHERE a.chart_id = %d";
                break;

            case 'artist':
                $sql = "SELECT * FROM {$wpdb->prefix}ff_chart_data a
                    INNER JOIN {$wpdb->prefix}ff_artists b ON b.id = a.item_id
                    WHERE a.chart_id = %d";
                break;

            case 'song':
                $sql = "SELECT a.*, b.*, c.title AS artist 
                    FROM {$wpdb->prefix}ff_chart_data a
                    INNER JOIN {$wpdb->prefix}ff_songs b ON b.id = a.item_id
                    INNER JOIN {$wpdb->prefix}ff_artists c ON c.id = b.artist
                    WHERE a.chart_id = %d";
                break;
            
            default:
                return array();
                break;
        }

        $num_item = (int) $settings['number_item'];
        $sql .= " LIMIT $num_item";
        $query = $wpdb->prepare($sql, $settings['chart_id']);
        $items = $wpdb->get_results($query);
        return $items;
    }
}