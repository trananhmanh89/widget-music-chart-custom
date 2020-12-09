<?php
defined('WPINC') or die('error');

$layouts = $this->getListLayout();
$currentLayout = !empty($instance['layout']) ? $instance['layout'] : 'default';
$title = !empty($instance['title']) ? $instance['title'] : '';
$chart_id = !empty($instance['chart_id']) ? $instance['chart_id'] : '0';
$number_item = !empty($instance['number_item']) ? $instance['number_item'] : '10';
$wpdb = ff_get_db();
$charts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ff_charts ORDER BY `type`, id DESC");
?>
<div class="music-chart-widget-admin-form">
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html__('Title:', 'widget-music-chart-custom'); ?></label>
        <input 
            class="widefat" 
            id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
            type="text" 
            value="<?php echo esc_attr($title); ?>">
    </p>
    <p class="chart-type">
        <label for="<?php echo esc_attr($this->get_field_id('chart_id')); ?>"><?php echo esc_html__('Chart Type:', 'widget-music-chart-custom'); ?></label>
        <select 
            class="widefat"
            name="<?php echo esc_attr($this->get_field_name('chart_id')); ?>" 
            id="<?php echo esc_attr($this->get_field_id('chart_id')); ?>">
            <option value="">-- Select Chart --</option>
            <?php foreach ($charts as $chart): ?>
                <option value="<?php echo $chart->id ?>" <?php echo selected($chart_id, $chart->id) ?>>
                    <?php echo '[' . $chart->type . '] - ' . $chart->title ?>
                </option>
            <?php endforeach ?>
        </select>
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('number_item')); ?>"><?php echo'Number Item:'; ?></label>
        <input 
            class="widefat" 
            id="<?php echo esc_attr($this->get_field_id('number_item')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('number_item')); ?>" 
            type="number" 
            value="<?php echo esc_attr($number_item); ?>">
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>"><?php echo esc_html__('Layout:', 'widget-music-chart-custom'); ?></label>
        <select 
            class="widefat"
            name="<?php echo esc_attr($this->get_field_name('layout')); ?>" 
            id="<?php echo esc_attr($this->get_field_id('layout')); ?>">
            <?php foreach ($layouts as $layout): ?>
                <option value="<?php echo $layout ?>" <?php selected($currentLayout, $layout) ?>>
                    <?php echo $layout ?>
                </option>
            <?php endforeach ?>
        </select>
    </p>
</div>
<script>
    typeof initChartSource === 'function' && initChartSource();
</script>