<?php
/*
Plugin Name: Widget Music Chart Custom
Plugin URI: https://wordpress.org/plugins/widget-music-chart-custom/
Description: Allows you to show charts from billboard.com or officialcharts.com
Version: 1.0
Author: Mr. Meo
Author URI: https://github.com/trananhmanh89/
*/

defined('WPINC') or die('error');

class WidgetMusicChartCustom_Widget extends WP_Widget
{
	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="widget-wrap">',
		'after_widget'  => '</div></div>'
	);

	protected $layoutPaths = array();

	function __construct()
	{
		$this->layoutPaths[] = get_template_directory() . '/widget-music-chart-custom/';
		$this->layoutPaths[] = __DIR__ . '/layouts/';

		wp_enqueue_style('widget-music-chart-custom-css', plugins_url('assets/widget-music-chart-custom.css', __FILE__));
		parent::__construct(
			'widget-music-chart-custom',
			'Music Chart Widget Custom'
		);
	}

	protected function getListLayout()
	{
		$layouts = array();
		foreach ($this->layoutPaths as $path) {
			if (!is_dir($path)) {
				continue;
			}

			$items = list_files($path, 1);
			foreach ($items as $item) {
				$info = pathinfo($item);
				if (
					isset($info['extension'])
					&& $info['extension'] === 'php'
					&& !in_array($info['filename'], $layouts)
				) {

					$layouts[] = $info['filename'];
				}
			}
		}

		sort($layouts);

		return $layouts;
	}

	protected function getLayoutPath($layout)
	{
		foreach ($this->layoutPaths as $path) {
			$file = $path . $layout . '.php';
			if (is_file($file)) {
				return $file;
			}
		}

		return __DIR__ . '/layouts/default.php';
	}

	public function widget($args, $instance)
	{
		require_once __DIR__ . '/helper.php';
		$layout = !empty($instance['layout']) ? $instance['layout'] : 'default';

		include $this->getLayoutPath($layout);
	}

	public function form($instance)
	{
		wp_enqueue_script('jquery');
		wp_enqueue_script('widget-music-chart-custom-admin', plugins_url('assets/widget-music-chart-custom-admin.js', __FILE__));
		include __DIR__ . '/admin/widget-form.php';
	}

	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['chart_id'] = (!empty($new_instance['chart_id'])) ? strip_tags($new_instance['chart_id']) : '0';
		$instance['number_item'] = (!empty($new_instance['number_item'])) ? $new_instance['number_item'] : '10';
		$instance['layout'] = (!empty($new_instance['layout'])) ? $new_instance['layout'] : 'default';

		return $instance;
	}
}

add_action('admin_init', function() {
	$wpdb = ff_get_db();
	$chart = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ff_charts';");
	if (!$chart) {
		$wpdb->query("CREATE TABLE `wp_ff_charts` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_unicode_ci',
			`type` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_unicode_ci',
			PRIMARY KEY (`id`),
			INDEX `type` (`type`)
		)
		COLLATE='utf8mb4_unicode_ci'
		ENGINE=InnoDB
		;");
	}

	$artist = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ff_artists';");
	if (!$artist) {
		$wpdb->query("CREATE TABLE `{$wpdb->prefix}ff_artists` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(255) NOT NULL,
			`artwork` VARCHAR(255) NOT NULL,
			`link` VARCHAR(200) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `link` (`link`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		;
		");
	}

	$albums = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ff_albums';");
	if (!$albums) {
		$wpdb->query("CREATE TABLE `{$wpdb->prefix}ff_albums` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`artist` INT(11) NOT NULL,
			`title` VARCHAR(255) NOT NULL,
			`artwork` VARCHAR(255) NOT NULL,
			`lastfm` VARCHAR(200) NOT NULL,
			`youtube` VARCHAR(255) NOT NULL,
			`spotify` VARCHAR(255) NOT NULL,
			`amazon` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `lastfm` (`lastfm`),
			INDEX `artist` (`artist`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		;
		");
	}

	$songs = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ff_songs';");
	if (!$songs) {
		$wpdb->query("CREATE TABLE `{$wpdb->prefix}ff_songs` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`artist` INT(11) NOT NULL,
			`title` VARCHAR(255) NOT NULL,
			`artwork` VARCHAR(255) NOT NULL,
			`lastfm` VARCHAR(200) NOT NULL,
			`youtube` VARCHAR(200) NOT NULL,
			`spotify` VARCHAR(200) NOT NULL,
			`amazon` VARCHAR(200) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `lastfm` (`lastfm`),
			INDEX `artist` (`artist`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		;
		");
	}

	$data = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ff_chart_data';");
	if (!$data) {
		$wpdb->query("CREATE TABLE `{$wpdb->prefix}ff_chart_data` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`chart_id` INT(11) NOT NULL,
			`item_id` INT(11) NOT NULL,
			`position` INT(4) NOT NULL,
			`last` INT(4) NOT NULL,
			`peak` INT(4) NOT NULL,
			`total` INT(4) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE INDEX `chart_id_item_id` (`chart_id`, `item_id`),
			INDEX `chart_id` (`chart_id`),
			INDEX `item_id` (`item_id`)
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		;
		");
	}
});

add_action('widgets_init', function () {
	register_widget('WidgetMusicChartCustom_Widget');
});

function register_ff_music_chart_page()
{
	add_menu_page('FF Music Chart', 'FF Music Chart', 'manage_options', 'ff-music-chart', 'ff_music_chart_page', 'dashicons-format-audio', 90);
}

add_action('admin_menu', 'register_ff_music_chart_page');

function ff_music_chart_page()
{
	wp_enqueue_script('jquery');
	wp_enqueue_script('ff-api', plugins_url('assets/api.js', __FILE__));
	wp_enqueue_script('ff-manager-app', plugins_url('assets/manager-app/dist/app.js', __FILE__));
	wp_enqueue_style('ff-manager-app', plugins_url('assets/manager-app/dist/app.css', __FILE__));
	require_once __DIR__ . '/admin/manage.php';
}

require_once __DIR__ . '/api.php';