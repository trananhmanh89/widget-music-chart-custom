(function () {
    function ajax(data) {
        return new Promise((resolve, reject) => {
            jQuery
                .ajax({
                    url: ajaxurl,
                    method: 'post',
                    dataType: 'json',
                    data: data
                })
                .done(res => {
                    resolve(res);
                })
                .fail(error => {
                    reject(error);
                })
        });
    }

    function saveArtist({id, title, artwork, link}) {
        return ajax({
            action: 'ff_save_artist',
            id,
            title,
            artwork,
            link,
        })
    }

    function getListArtist({page, search}) {
        return ajax({
            action: 'ff_get_list_artist',
            search,
            page,
        });
    }

    function fetchThumb(link) {
        return ajax({
            action: 'ff_fetch_thumb',
            link,
        });
    }

    function saveNewArtist({ title, link, thumb }) {
        return ajax({
            action: 'ff_save_new_artist',
            title,
            link,
            thumb,
        });
    }

    function saveNewSong(data) {
        const {
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        } = data;

        return ajax({
            action: 'ff_save_new_song',
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        })
    }

    function getListSong({page, search}) {
        return ajax({
            action: 'ff_get_list_song',
            search,
            page,
        });
    }

    function saveSong(data)
    {
        const {
            id,
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        } = data;

        return ajax({
            action: 'ff_save_song',
            id,
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        })
    }

    function saveNewAlbum(data)
    {
        const {
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        } = data;

        return ajax({
            action: 'ff_save_new_album',
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        })
    }

    function getListAlbum({page, search}) {
        return ajax({
            action: 'ff_get_list_album',
            search,
            page,
        });
    }

    function saveAlbum(data)
    {
        const {
            id,
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        } = data;

        return ajax({
            action: 'ff_save_album',
            id,
            title,
            artist,
            artwork,
            lastfm,
            youtube,
            spotify,
            amazon,
        })
    }

    function saveNewChart({type, title})
    {
        return ajax({
            action: 'ff_save_new_chart',
            type,
            title,
        })
    }

    function getListChart({page, search, type}) {
        return ajax({
            action: 'ff_get_list_chart',
            search,
            page,
            type,
        });
    }

    function saveChart({id, title}) {
        return ajax({
            action: 'ff_save_chart',
            id,
            title,
        })
    }

    function getChartData(id) {
        return ajax({
            action: 'ff_get_chart_data',
            id,
        });
    }

    function removeChartItem({chart_id, item_id}) {
        return ajax({
            action: 'ff_remove_chart_item',
            chart_id,
            item_id,
        });
    }

    function saveChartData({chart_id, chart_title, data}) {
        return ajax({
            action: 'ff_save_chart_data',
            chart_id,
            chart_title,
            data,
        });
    }

    function deleteChart(chart_id) {
        return ajax({
            action: 'ff_delete_chart',
            chart_id,
        });
    }

    function deleteAllChartItems(chart_id) {
        return ajax({
            action: 'ff_delete_all_chart_items',
            chart_id,
        });
    }

    window.ff_api = {
        getListArtist,
        fetchThumb,
        saveNewArtist,
        saveArtist,
        saveNewSong,
        getListSong,
        saveSong,
        saveNewAlbum,
        getListAlbum,
        saveAlbum,
        saveNewChart,
        getListChart,
        getChartData,
        removeChartItem,
        saveChartData,
        deleteChart,
        deleteAllChartItems,
    }
})();

