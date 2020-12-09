<?php
defined('WPINC') or die('error');

/**
 * @return wpdb
 */
function ff_get_db()
{
	global $wpdb;
	return $wpdb;
}

add_action('wp_ajax_ff_fetch_thumb', 'ff_fetch_thumb');
function ff_fetch_thumb()
{
	$link = filter_input(INPUT_POST, 'link');
	if (!preg_match('/^(http|https):\/\/.+?\..+/', $link)) {
		die(json_encode(array('error' => 'link format error')));
	}

	$res = wp_remote_get($link);

	if ($res['response']['code'] !== 200) {
		die(json_encode(array('error' => 'Could not open link.')));
	}

	preg_match('/<meta.*?property="og:image".*?content="(.*?)".*?>/', $res['body'], $matches);
	preg_match('/data-page-resource-name="(.*?)"/', $res['body'], $matches2);

	if (isset($matches[1]) && isset($matches2[1])) {
		die(json_encode(array(
			'thumb' => $matches[1],
			'name' => html_entity_decode($matches2[1], ENT_QUOTES),
		)));
	}

	die(json_encode(array('error' => 'Thumb not found')));
}

add_action('wp_ajax_ff_save_new_artist', 'ff_save_new_artist');
function ff_save_new_artist()
{
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$link = (trim(filter_input(INPUT_POST, 'link')));
	$thumb = (trim(filter_input(INPUT_POST, 'thumb')));

	$wpdb = ff_get_db();
	$query = "SELECT COUNT(*) FROM `{$wpdb->prefix}ff_artists` WHERE link = '" . esc_sql($link) . "'";
	$count = $wpdb->get_var($query);
	if ($count) {
		die(json_encode(array('error' => 'Aritst is existed')));
	}

	$result = $wpdb->insert(
		$wpdb->prefix . 'ff_artists',
		array(
			'title' => $title,
			'link' => $link,
			'artwork' => $thumb,
		)
	);

	if ($result) {
		die(json_encode(array('success' => true)));
	} else {
		die(json_encode(array('error' => 'Create new artist error')));
	}
}

add_action('wp_ajax_ff_get_list_artist', 'ff_get_list_artist');
function ff_get_list_artist()
{
	$search = esc_sql(trim(filter_input(INPUT_POST, 'search')));

	$wpdb = ff_get_db();
	$query = "FROM `{$wpdb->prefix}ff_artists` WHERE 1=1";
	if ($search) {
		$query .= " AND `title` LIKE '%$search%'";
	}

	$total = $wpdb->get_var("SELECT COUNT(*) $query");

	$query .= " ORDER BY id DESC";

	$page = (int) filter_input(INPUT_POST, 'page');
	$page = $page ? $page : 1;
	$page = $page - 1;
	$limit = 20;
	$offset = $page * $limit;
	$query .= " LIMIT $offset, $limit";

	$rows = $wpdb->get_results("SELECT * $query");

	die(json_encode(array(
		'list' => $rows,
		'total' => $total,
	)));
}

add_action('wp_ajax_ff_save_artist', 'ff_save_artist');
function ff_save_artist()
{
	$id = (int) (trim(filter_input(INPUT_POST, 'id')));
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$link = (trim(filter_input(INPUT_POST, 'link')));
	$artwork = (trim(filter_input(INPUT_POST, 'artwork')));

	$wpdb = ff_get_db();
	$wpdb->update(
		$wpdb->prefix . 'ff_artists',
		array(
			'title' => $title,
			'link' => $link,
			'artwork' => $artwork
		),
		array(
			'id' => $id
		)
	);

	die(json_encode(array('done' => 'true')));

}

add_action('wp_ajax_ff_save_new_song', 'ff_save_new_song');
function ff_save_new_song()
{
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$artist = (trim(filter_input(INPUT_POST, 'artist')));
	$artwork = (trim(filter_input(INPUT_POST, 'artwork')));
	$lastfm = (trim(filter_input(INPUT_POST, 'lastfm')));
	$youtube = (trim(filter_input(INPUT_POST, 'youtube')));
	$spotify = (trim(filter_input(INPUT_POST, 'spotify')));
	$amazon = (trim(filter_input(INPUT_POST, 'amazon')));

	$wpdb = ff_get_db();
	$query = "SELECT COUNT(*) FROM `{$wpdb->prefix}ff_songs` WHERE lastfm = '" . esc_sql($lastfm) . "'";
	$count = $wpdb->get_var($query);
	if ($count) {
		die(json_encode(array('error' => 'Song is existed')));
	}

	$result = $wpdb->insert(
		$wpdb->prefix . 'ff_songs',
		array(
			'title' => $title,
			'artist' => $artist,
			'artwork' => $artwork,
			'lastfm' => $lastfm,
			'youtube' => $youtube,
			'spotify' => $spotify,
			'amazon' => $amazon,
		)
	);

	if ($result) {
		die(json_encode(array('success' => true)));
	} else {
		die(json_encode(array('error' => 'Create new song error')));
	}
}

add_action('wp_ajax_ff_get_list_song', 'ff_get_list_song');
function ff_get_list_song()
{
	$search = esc_sql(trim(filter_input(INPUT_POST, 'search')));

	$wpdb = ff_get_db();
	$query = "FROM `{$wpdb->prefix}ff_songs` a 
		INNER JOIN `{$wpdb->prefix}ff_artists` b ON a.artist = b.id
		WHERE 1=1";
	if ($search) {
		$frags = explode('|', $search);
		if (count($frags) === 2) {
			$song = esc_sql(trim($frags[0]));
			$artist = esc_sql(trim($frags[1]));

			$query .= " AND a.`title` LIKE '%$song%' AND b.`title` LIKE '%$artist%'";
		} else {
			$query .= " AND (a.`title` LIKE '%$search%' OR b.`title` LIKE '%$search%')";
		}
	}

	$total = $wpdb->get_var("SELECT COUNT(*) $query");

	$query .= " ORDER BY a.id DESC";

	$page = (int) filter_input(INPUT_POST, 'page');
	$page = $page ? $page : 1;
	$page = $page - 1;
	$limit = 20;
	$offset = $page * $limit;
	$query .= " LIMIT $offset, $limit";

	$rows = $wpdb->get_results("SELECT a.*, b.`title` AS artist_name $query");

	die(json_encode(array(
		'list' => $rows,
		'total' => $total,
	)));
}

add_action('wp_ajax_ff_save_song', 'ff_save_song');
function ff_save_song()
{
	$id = (int) (trim(filter_input(INPUT_POST, 'id')));
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$artist = (trim(filter_input(INPUT_POST, 'artist')));
	$artwork = (trim(filter_input(INPUT_POST, 'artwork')));
	$lastfm = (trim(filter_input(INPUT_POST, 'lastfm')));
	$youtube = (trim(filter_input(INPUT_POST, 'youtube')));
	$spotify = (trim(filter_input(INPUT_POST, 'spotify')));
	$amazon = (trim(filter_input(INPUT_POST, 'amazon')));

	$wpdb = ff_get_db();
	$wpdb->update(
		$wpdb->prefix . 'ff_songs',
		array(
			'title' => $title,
			'artist' => $artist,
			'artwork' => $artwork,
			'lastfm' => $lastfm,
			'youtube' => $youtube,
			'spotify' => $spotify,
			'amazon' => $amazon,
		),
		array(
			'id' => $id
		)
	);

	die(json_encode(array('done' => 'true')));
}

add_action('wp_ajax_ff_save_new_album', 'ff_save_new_album');
function ff_save_new_album()
{
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$artist = (trim(filter_input(INPUT_POST, 'artist')));
	$artwork = (trim(filter_input(INPUT_POST, 'artwork')));
	$lastfm = (trim(filter_input(INPUT_POST, 'lastfm')));
	$youtube = (trim(filter_input(INPUT_POST, 'youtube')));
	$spotify = (trim(filter_input(INPUT_POST, 'spotify')));
	$amazon = (trim(filter_input(INPUT_POST, 'amazon')));

	$wpdb = ff_get_db();
	$query = "SELECT COUNT(*) FROM `{$wpdb->prefix}ff_albums` WHERE lastfm = '" . esc_sql($lastfm) . "'";
	$count = $wpdb->get_var($query);
	if ($count) {
		die(json_encode(array('error' => 'Song is existed')));
	}

	$result = $wpdb->insert(
		$wpdb->prefix . 'ff_albums',
		array(
			'title' => $title,
			'artist' => $artist,
			'artwork' => $artwork,
			'lastfm' => $lastfm,
			'youtube' => $youtube,
			'spotify' => $spotify,
			'amazon' => $amazon,
		)
	);

	if ($result) {
		die(json_encode(array('success' => true)));
	} else {
		die(json_encode(array('error' => 'Create new album error')));
	}
}

add_action('wp_ajax_ff_get_list_album', 'ff_get_list_album');
function ff_get_list_album()
{
	$search = esc_sql(trim(filter_input(INPUT_POST, 'search')));

	$wpdb = ff_get_db();
	$query = "FROM `{$wpdb->prefix}ff_albums` a 
		INNER JOIN `{$wpdb->prefix}ff_artists` b ON a.artist = b.id
		WHERE 1=1";
	if ($search) {
		$frags = explode('|', $search);
		if (count($frags) === 2) {
			$album = esc_sql(trim($frags[0]));
			$artist = esc_sql(trim($frags[1]));

			$query .= " AND a.`title` LIKE '%$album%' AND b.`title` LIKE '%$artist%'";
		} else {
			$query .= " AND (a.`title` LIKE '%$search%' OR b.`title` LIKE '%$search%')";
		}
	}

	$total = $wpdb->get_var("SELECT COUNT(*) $query");

	$query .= " ORDER BY a.id DESC";

	$page = (int) filter_input(INPUT_POST, 'page');
	$page = $page ? $page : 1;
	$page = $page - 1;
	$limit = 20;
	$offset = $page * $limit;
	$query .= " LIMIT $offset, $limit";

	$rows = $wpdb->get_results("SELECT a.*, b.`title` AS artist_name $query");

	die(json_encode(array(
		'list' => $rows,
		'total' => $total,
	)));
}

add_action('wp_ajax_ff_save_album', 'ff_save_album');
function ff_save_album()
{
	$id = (int) (trim(filter_input(INPUT_POST, 'id')));
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$artist = (trim(filter_input(INPUT_POST, 'artist')));
	$artwork = (trim(filter_input(INPUT_POST, 'artwork')));
	$lastfm = (trim(filter_input(INPUT_POST, 'lastfm')));
	$youtube = (trim(filter_input(INPUT_POST, 'youtube')));
	$spotify = (trim(filter_input(INPUT_POST, 'spotify')));
	$amazon = (trim(filter_input(INPUT_POST, 'amazon')));

	$wpdb = ff_get_db();
	$wpdb->update(
		$wpdb->prefix . 'ff_albums',
		array(
			'title' => $title,
			'artist' => $artist,
			'artwork' => $artwork,
			'lastfm' => $lastfm,
			'youtube' => $youtube,
			'spotify' => $spotify,
			'amazon' => $amazon,
		),
		array(
			'id' => $id
		)
	);

	die(json_encode(array('done' => 'true')));
}

add_action('wp_ajax_ff_save_new_chart', 'ff_save_new_chart');
function ff_save_new_chart()
{
	$title = (trim(filter_input(INPUT_POST, 'title')));
	$type = (trim(filter_input(INPUT_POST, 'type')));

	$wpdb = ff_get_db();

	$result = $wpdb->insert(
		$wpdb->prefix . 'ff_charts',
		array(
			'title' => $title,
			'type' => $type,
		)
	);

	if ($result) {
		die(json_encode(array('success' => true)));
	} else {
		die(json_encode(array('error' => 'Create chart error')));
	}
}

add_action('wp_ajax_ff_get_list_chart', 'ff_get_list_chart');
function ff_get_list_chart()
{
	$search = esc_sql(trim(filter_input(INPUT_POST, 'search')));
	$type = esc_sql(trim(filter_input(INPUT_POST, 'type')));

	$wpdb = ff_get_db();
	$query = "FROM `{$wpdb->prefix}ff_charts` WHERE 1=1";

	if ($type) {
		$query .= " AND `type` = '$type'";
	}

	if ($search) {
		$query .= " AND `title` LIKE '%$search%'";
	}

	$total = $wpdb->get_var("SELECT COUNT(*) $query");

	$query .= " ORDER BY id DESC";

	$page = (int) filter_input(INPUT_POST, 'page');
	$page = $page ? $page : 1;
	$page = $page - 1;
	$limit = 20;
	$offset = $page * $limit;
	$query .= " LIMIT $offset, $limit";

	$rows = $wpdb->get_results("SELECT * $query");

	die(json_encode(array(
		'list' => $rows,
		'total' => $total,
	)));
}

add_action('wp_ajax_ff_get_chart_data', 'ff_get_chart_data');
function ff_get_chart_data()
{
	$id = (int) (trim(filter_input(INPUT_POST, 'id')));

	$wpdb = ff_get_db();
	$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}ff_charts WHERE id = %d", $id);
	$chart = $wpdb->get_row($query);
	if (!$chart) {
		die(json_encode(array('error' => 'chart not found')));
	}

	$type = $chart->type;
	if ($type === 'album') {
		$sql = "SELECT 
				a.chart_id,
				a.item_id,
				a.position,
				a.last,
				a.peak,
				a.total,
				b.title AS item_title,
				b.artwork,
				c.title AS artist_name
			FROM {$wpdb->prefix}ff_chart_data a
			INNER JOIN {$wpdb->prefix}ff_albums b ON b.id = a.item_id
			INNER JOIN {$wpdb->prefix}ff_artists c on c.id = b.artist
			WHERE chart_id = %d
			ORDER BY a.position ASC";
	} else if ($type === 'song') {
		$sql = "SELECT 
				a.chart_id,
				a.item_id,
				a.position,
				a.last,
				a.peak,
				a.total,
				b.title AS item_title,
				b.artwork,
				c.title AS artist_name
			FROM {$wpdb->prefix}ff_chart_data a
			INNER JOIN {$wpdb->prefix}ff_songs b ON b.id = a.item_id
			INNER JOIN {$wpdb->prefix}ff_artists c on c.id = b.artist
			WHERE chart_id = %d
			ORDER BY a.position ASC";
	} else {
		$sql = "SELECT * 
			FROM {$wpdb->prefix}ff_chart_data a
			INNER JOIN {$wpdb->prefix}ff_artists b ON a.item_id = b.id
			WHERE a.chart_id = %d
			ORDER BY a.position ASC";
	}
	
	$query = $wpdb->prepare($sql, $id);
	$results = $wpdb->get_results($query);
	$list = array_map(function($item) use ($type) {
		if ($type === 'album' || $type === 'song') {
			$item->title = $item->item_title . ' — [' . $item->artist_name . ']';
		}

		return $item;
	}, $results);

	die(json_encode(array(
		'list' => $list,
	)));
}

add_action('wp_ajax_ff_remove_chart_item', 'ff_remove_chart_item');
function ff_remove_chart_item()
{
	$chart_id = (int) (trim(filter_input(INPUT_POST, 'chart_id')));
	$item_id = (int) (trim(filter_input(INPUT_POST, 'item_id')));

	$wpdb = ff_get_db();
	$wpdb->delete("{$wpdb->prefix}ff_chart_data", array(
		'chart_id' => $chart_id,
		'item_id' => $item_id,
	));

	die(json_encode(array('done' => true)));
}

add_action('wp_ajax_ff_save_chart_data', 'ff_save_chart_data');
function ff_save_chart_data()
{
	$chart_id = (int) (trim(filter_input(INPUT_POST, 'chart_id')));
	$wpdb = ff_get_db();
	$query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}ff_charts WHERE id = %d", $chart_id);
	$chart = $wpdb->get_var($query);
	if (!$chart) {
		die(json_encode(array('error' => 'chart not found')));
	}

	$chart_title = esc_sql(trim(filter_input(INPUT_POST, 'chart_title')));
	$wpdb->update(
		$wpdb->prefix . 'ff_charts',
		array(
			'title' => $chart_title,
		),
		array(
			'id' => $chart_id,
		)
	);

	$data = array_filter((array) $_POST['data'], function($item) {
		return $item;
	});

	$list = array_values($data);
	foreach ($list as $idx => $item) {
		$sql = "SELECT COUNT(*) 
			FROM {$wpdb->prefix}ff_chart_data 
			WHERE chart_id = %d 
			AND item_id = %d";
		$count = $wpdb->get_var($wpdb->prepare($sql, array($chart_id, $item['item_id'])));
		if ($count) {
			$wpdb->update(
				$wpdb->prefix . 'ff_chart_data',
				array(
					'position' => $idx + 1,
					'last' => (int) $item['last'],
					'peak' => (int) $item['peak'],
					'total' => (int) $item['total'],
				),
				array(
					'chart_id' => $chart_id,
					'item_id' => $item['item_id'],
				)
			);
		} else {
			$wpdb->insert(
				$wpdb->prefix . 'ff_chart_data',
				array(
					'chart_id' => $chart_id,
					'item_id' => $item['item_id'],
					'position' => $idx + 1,
					'last' => (int) $item['last'],
					'peak' => (int) $item['peak'],
					'total' => (int) $item['total'],
				)
			);
		}
	}
	
	die(json_encode(array('done' => true)));
}

add_action('wp_ajax_ff_delete_chart', 'ff_delete_chart');
function ff_delete_chart()
{
	$chart_id = (int) (trim(filter_input(INPUT_POST, 'chart_id')));
	$wpdb = ff_get_db();
	$wpdb->delete($wpdb->prefix . 'ff_charts', array('id' => $chart_id));
	$wpdb->delete($wpdb->prefix . 'ff_chart_data', array('chart_id' => $chart_id));
	die(json_encode(array('done' => true)));
}

add_action('wp_ajax_ff_delete_all_chart_items', 'ff_delete_all_chart_items');
function ff_delete_all_chart_items()
{
	$chart_id = (int) (trim(filter_input(INPUT_POST, 'chart_id')));
	$wpdb = ff_get_db();
	$wpdb->delete($wpdb->prefix . 'ff_chart_data', array('chart_id' => $chart_id));
	die(json_encode(array('done' => true)));
}

