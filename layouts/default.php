<?php
defined('WPINC') or die('error');
$data = MusicChartCustomHelper::getChartCustomData($instance);

echo $args['before_widget'];
if (!empty($instance['title'])) {
    echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
}

$items = array();
foreach ($data as $key => $value) {
    $item = new stdClass;
    $item->isLong = $key + 1 > 99 ? true : false;
    $item->title = $value->title;
    $item->subtitle = !empty($value->artist) ? $value->artist : '';
    $item->peak = (int) $value->peak;
    $item->last = (int) $value->last;
    $item->duration = (int) $value->total;
    $item->rank = (int) $value->position;
    $item->youtube = !empty($value->youtube) ? $value->youtube : '';
    $item->spotify = !empty($value->spotify) ? $value->spotify : '';
    $item->amazon = !empty($value->amazon) ? $value->amazon : '';

    $trend = $item->rank - $item->last;
    if ($item->duration == 1) {
        $item->trend = 'new';
    } else if ($item->duration > 1 && !$item->last) {
        $item->trend = 'reenter';
    } else if ($trend === 0) {
        $item->trend = 'steady';
    } else if ($trend < 0 ) {
        $item->trend = 'rising';
    } else {
        $item->trend = 'falling';
    }

    switch ($item->trend) {
        case 'rising':
            $item->trend_icon = '<img src="' . plugins_url('widget-music-chart-custom/assets/images/up.svg') . '" />';
            break;

        case 'falling':
            $item->trend_icon = '<img src="' . plugins_url('widget-music-chart-custom/assets/images/down.svg') . '" />';
            break;

        case 'steady':
            $item->trend_icon = '<img src="' . plugins_url('widget-music-chart-custom/assets/images/right.svg') . '" />';
            break;

        case 'reenter':
            $item->trend_icon = __('ReEnter', 'widget-music-chart-custom');
            break;

        default:
            $item->trend_icon = __('New', 'widget-music-chart-custom');
            break;
    }

    $promo = $item->last - $item->rank;

    if ($item->trend === 'new' || $item->trend === 'reenter' || $promo === 0) {
        $item->promo = '-';
    } else if ($promo > 0) {
        $item->promo = "+$promo";
    } else {
        $item->promo = $promo;
    }

    $item->image = $value->artwork;
    if (!$item->image) {
        $item->image = plugins_url('widget-music-chart-custom/assets/images/song-icon.jpg');
    }

    $items[] = $item;
}

?>
<div class="widget-<?php echo $this->id ?> ff-music-items">
    <?php foreach ($items as $key => $item): ?>
        <div class="ff-music-item">
            <div class="ff-music-item__rank <?php echo $item->isLong ? 'ff-music-item__rank--long' : '' ?>">
                <div class="rank__number"><?php echo $key + 1 ?></div>
                <div class="trend__icon color--<?php echo $item->trend ?>"><?php echo $item->trend_icon ?></div>
            </div>
            <div class="ff-music-item__detail">
                <div class="ff-music-item__title">
                    <?php echo $item->title ?>
                </div>
                <div class="ff-music-item__subtitle">
                    <?php echo $item->subtitle ?>
                </div>
                <div class="ff-music-item__link">
                    <?php if ($item->youtube): ?>
                        <span title="Youtube">
                            <a href="<?php echo $item->youtube ?>" target="_blank" rel="nofollow">
                                <img src="<?php echo plugins_url('widget-music-chart-custom/assets/images/youtube-square-brands.svg') ?>" alt="youtube" />
                            </a>
                        </span>
                    <?php endif ?>
                    <?php if ($item->spotify): ?>
                        <span title="Spotify">
                            <a href="<?php echo $item->spotify ?>" target="_blank" rel="nofollow">
                                <img src="<?php echo plugins_url('widget-music-chart-custom/assets/images/spotify-brands.svg') ?>" alt="youtube" />
                            </a>
                        </span>
                    <?php endif ?>
                    <?php if ($item->amazon): ?>
                        <span title="Amazon">
                            <a href="<?php echo $item->amazon ?>" target="_blank" rel="nofollow">
                                <img src="<?php echo plugins_url('widget-music-chart-custom/assets/images/amazon-brands.svg') ?>" alt="youtube" />
                            </a>
                        </span>
                    <?php endif ?>
                </div>
                <div class="ff-music-item__promo color--<?php echo $item->trend ?>">
                    <span><?php echo $item->promo ?></span>
                </div>
                <div class="ff-music-item__meta">
                    <span title="<?php _e('Last Week', 'widget-music-chart-custom') ?>">
                        <?php echo $item->last ?> <span class="ff-music-item__meta-label"><?php _e('Last', 'widget-music-chart-custom') ?></span>
                    </span> |
                    <span title="<?php _e('Peak', 'widget-music-chart-custom') ?>">
                        <?php echo $item->peak ?> <span class="ff-music-item__meta-label"><?php _e('Peak', 'widget-music-chart-custom') ?></span>
                    </span> |
                    <span title="<?php _e('Duration', 'widget-music-chart-custom') ?>">
                        <?php echo $item->duration ?> 
                        <span class="ff-music-item__meta-label">
                            <?php echo $item->duration < 2 ? _e('Week', 'widget-music-chart-custom') : _e('Weeks', 'widget-music-chart-custom') ?>
                        </span>
                    </span>
                </div>
            </div>
            <div class="ff-music-item__image">
                <img src="<?php echo $item->image ?>" alt="<?php echo $item->title ?>">
            </div>
        </div>
    <?php endforeach ?>
</div>
<?php
echo $args['after_widget'];
