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