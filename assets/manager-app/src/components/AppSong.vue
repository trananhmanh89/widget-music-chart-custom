<template>
    <div class="app-song">
        <div class="artist-toolbar">
            <el-button size="small" icon="el-icon-plus" @click="show = !show">New Song</el-button>
            <div class="new-song-container" v-if="show">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="song_title">Title *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="song_title" 
                                value="" 
                                class="regular-text" 
                                v-model="song_title" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="song_artist">Artist *</label>
                        </th>
                        <td>
                            <el-select
                                v-model="song_artist"
                                filterable
                                remote
                                placeholder="Enter artist name"
                                :remote-method="searchArtist"
                                >
                                <el-option 
                                    size="small"
                                    v-for="opt in options" 
                                    :key="opt.id"
                                    :label="opt.title"
                                    :value="opt.id" ></el-option>
                            </el-select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="last_fm">Last.fm *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="last_fm" 
                                value="" 
                                class="regular-text" 
                                v-model="song_lastfm" 
                                :disabled="saving">
                            <el-button 
                                size="small" 
                                icon="el-icon-circle-check" 
                                :disabled="!song_lastfm || saving"
                                :loading="fetching"
                                @click="fetchThumb">Fetch Thumb</el-button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="song_artwork">Artwork *</label>
                        </th>
                        <td>
                            <input type="text" id="song_artwork" class="regular-text"  v-model="song_artwork" :disabled="saving">
                            <br>
                            <br>
                            <img v-if="song_artwork"  class="new-song-thumb" :src="song_artwork" alt="">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="song_youtube">Youtube</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="song_youtube" 
                                value="" 
                                class="regular-text" 
                                v-model="song_youtube" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="song_spotify">Spotify</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="song_spotify" 
                                value="" 
                                class="regular-text" 
                                v-model="song_spotify" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="song_amazon">Amazon</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="song_amazon" 
                                value="" 
                                class="regular-text" 
                                v-model="song_amazon" 
                                :disabled="saving">
                        </td>
                    </tr>
                </table>
                <el-button 
                    size="small" 
                    type="success" 
                    icon="el-icon-circle-check" 
                    :disabled="!song_title || !song_artist || !song_artwork"
                    :loading="saving"
                    @click="saveNewSong">Save</el-button>
            </div>
        </div>
        <hr>
        <div class="song-search">
            <el-input 
                placeholder="Song or artist title..." 
                style="width: 20rem"
                size="small"
                clearable
                class="regular-text"
                v-model="search" 
                @change="searchSong" />

            <el-popover
                placement="top-start"
                title="Advanced Search"
                width="300"
                trigger="hover">
                <div>- Use syntax "song_name | artist_name" to search song by artist.</div>
                <div>- Ex: Dynamite | BTS </div>
                <i class="el-icon-question" slot="reference"></i>
            </el-popover>
        </div>
        <div class="song-list">
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                    <th width="10%">Artwork</th>
                    <th>Info</th>
                    <th>Link</th>
                    <th width="5%">ID</th>
                    <th width="10%">Action</th>
                </thead>
                <tbody>
                    <tr v-for="item in list" :key="item.id">
                        <th>
                            <a target="_blank" :href="item.artwork">
                                <img class="song-photo" v-lazy="item.artwork" alt="">
                            </a>
                        </th>
                        <th>
                            <div v-if="edit !== item.id"><strong>{{item.title}} — [{{item.artist_name}}]</strong></div>
                            <div v-if="edit === item.id">
                                <div>Title</div>
                                <input class="regular-text" type="text" v-model="item.title" :disabled="saving">
                                <br>
                                <br>
                                <div>Artist</div>
                                <el-select
                                    v-model="item.artist"
                                    size="small"
                                    filterable
                                    remote
                                    placeholder="Enter artist name"
                                    :remote-method="searchArtist"
                                    @change="value => onArtistChange(value, item)"
                                    >
                                    <el-option 
                                        size="small"
                                        v-for="opt in options" 
                                        :key="opt.id"
                                        :label="opt.title"
                                        :value="opt.id" ></el-option>
                                </el-select>
                                <br>
                                <br>
                                <div>Artwork</div>
                                <input class="regular-text" type="text" v-model="item.artwork" :disabled="saving">
                            </div>
                        </th>
                        <th>
                            <div v-if="edit !== item.id">
                                <a target="_blank" :href="item.lastfm">{{item.lastfm}}</a>
                            </div>
                            <div v-if="edit === item.id">
                                <div>Last.fm</div>
                                <div style="display: flex">
                                    <input class="regular-text" type="text" v-model="item.lastfm" :disabled="saving">
                                    <el-button 
                                        style="margin-left: .5rem;"
                                        size="small" 
                                        icon="el-icon-circle-check" 
                                        :loading="fetching"
                                        :disabled="saving"
                                        @click="fetchSongThumb(item)">Fetch Thumb</el-button>
                                </div>
                                <br>
                                <div>Youtube</div>
                                <div>
                                    <input class="regular-text" type="text" v-model="item.youtube" :disabled="saving">
                                </div>
                                <br>
                                <div>Spotify</div>
                                <div>
                                    <input class="regular-text" type="text" v-model="item.spotify" :disabled="saving">
                                </div>
                                <br>
                                <div>Amazon</div>
                                <div>
                                    <input class="regular-text" type="text" v-model="item.amazon" :disabled="saving">
                                </div>
                            </div>
                        </th>
                        <th><strong>{{item.id}}</strong></th>
                        <th>
                            <el-button 
                                size="small" 
                                icon="el-icon-circle-check" 
                                :loading="edit === item.id && saving"
                                @click="edit !== item.id ? editSong(item) : saveSong(item)">
                                {{edit !== item.id ? 'Edit' : 'Save'}}
                            </el-button>
                        </th>
                    </tr>
                </tbody>
            </table>
            <br>
            <el-pagination
                background
                layout="prev, pager, next"
                hide-on-single-page
                :page-size="page_size"
                :total="total"
                :current-page="page"
                @current-change="onPageChange">
            </el-pagination>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';

export default {
    data() {
        return {
            options: [],
            show: false,
            song_title: '',
            song_artist: '',
            song_artwork: '',
            song_lastfm: '',
            song_youtube: '',
            song_spotify: '',
            song_amazon: '',
            fetching: false,
            saving: false,
            search: '',
            search_artist: '',
            page: 1,
            page_size: 20,
            total: 0,
            list: [],
            edit: '0',
        }
    },

    mounted() {
        this.getList();
    },

    methods: {
        searchSong() {
            this.page = 1;
            this.getList();
        },

        searchArtist(query) {
            ff_api.getListArtist({page: 1, search: query}).then(res => {
                Vue.set(this, 'options', res.list);
            })
            .catch(error => {
                return alert('search artist error');
            })
        },

        onPageChange(num) {
            this.page = num;
            this.getList().then(() => {
                document.documentElement.scrollTop = 0
            });
        },

        fetchSongThumb(item) {
            this.fetching = true;
            const {lastfm} = item;
            ff_api.fetchThumb(lastfm).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                item.artwork = res.thumb;
                item.title = res.name;
                this.success();
            })
            .catch(error => {
                alert('fetch song thumb error');
            })
            .finally(() => {
                this.fetching = false;
            });
        },

        editSong({id, artist, artist_name}) {
            if (this.fetching) {
                return alert('Fetching thumb! Please wait.')
            }

            if (this.saving) {
                return alert('Saving song! Please wait.')
            }

            this.song_artist = '';
            this.edit = id;

            Vue.set(this, 'options', [{
                id: artist,
                title: artist_name
            }]);
        },

        saveSong(item) {
            if (this.fetching) {
                return alert('Fetching thumb! Please wait.')
            }

            if (this.saving) {
                return alert('Saving Song! Please wait.')
            }

            this.saving = true;
            ff_api.saveSong(item).then(res => {
                this.saving = false;
                this.edit = '0';
                this.success();
            })
            .catch(() => alert('save song error'))
        },

        onArtistChange(value, item) {
            const option = this.options.find(opt => opt.id === value);

            item.artist = option.id;
            item.artist_name = option.title;
        },

        getList() {
            const params = {
                search: this.search.trim(),
                page: this.page
            };

            return ff_api.getListSong(params).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                Vue.set(this, 'list', res.list);
                this.total = +res.total;
                this.$store.commit('loading', false);
            });
        },

        fetchThumb() {
            this.fetching = true;
            ff_api.fetchThumb(this.song_lastfm).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                this.song_artwork = res.thumb;
                this.song_title = res.name;
            })
            .catch(error => {
                alert('fetch song thumb error');
            })
            .finally(() => {
                this.fetching = false;
            });
        },

        saveNewSong() {
            this.saving = true;

            ff_api.saveNewSong({
                title: this.song_title,
                artist: this.song_artist,
                artwork: this.song_artwork,
                lastfm: this.song_lastfm,
                youtube: this.song_youtube,
                spotify: this.song_spotify,
                amazon: this.song_amazon,
            })
            .then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                if (res.success) {
                    this.success();
                    return this.getList().then(() => {
                        this.reset()
                    });
                }
            })
            .catch(error => {
                console.log(error);
                alert('save error');
            })
            .finally(() => {
                this.saving = false;
            })
        },

        reset() {
            this.song_title = '';
            this.song_artist = '';
            this.song_artwork = '';
            this.song_lastfm = '';
            this.song_youtube = '';
            this.song_spotify = '';
            this.song_amazon = '';
            this.edit = 0;
        },

        success() {
            return this.$message({
                message: 'success',
                type: 'success',
                offset: 50,
                showClose: true,
            })
        }
    }
}
</script>

<style lang="scss">
.app-song {
    .new-song-container {
        margin-top: 1rem;
    }

    .new-song-thumb {
        width: 10rem;
    }

    .song-search {
        margin-top: 1rem;
    }

    .song-list {
        margin-top: 1rem;

        input {
            max-width: 20rem;
            width: 100%;
            margin: 0;
        }
    }

    .song-photo {
        max-width: 5rem;
        width: 100%;
    }

    .el-select input {
        background-color: #fff;
    }
}
</style>