<template>
    <div class="app-album">
        <div class="album-toolbar">
            <el-button size="small" icon="el-icon-plus" @click="show = !show">New Album</el-button>
            <div class="new-album-container" v-if="show">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="album_title">Title *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="album_title" 
                                value="" 
                                class="regular-text" 
                                v-model="album_title" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="album_artist">Artist *</label>
                        </th>
                        <td>
                            <el-select
                                v-model="album_artist"
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
                                v-model="album_lastfm" 
                                :disabled="saving">
                            <el-button 
                                size="small" 
                                icon="el-icon-circle-check" 
                                :disabled="!album_lastfm || saving"
                                :loading="fetching"
                                @click="fetchThumb">Fetch Thumb</el-button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="album_artwork">Artwork *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="album_artwork" 
                                value="" 
                                class="regular-text" 
                                v-model="album_artwork" 
                                :disabled="saving">
                            <br>
                            <br>
                            <img v-if="album_artwork"  class="new-album-thumb" :src="album_artwork" alt="">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="album_youtube">Youtube</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="album_youtube" 
                                value="" 
                                class="regular-text" 
                                v-model="album_youtube" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="album_spotify">Spotify</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="album_spotify" 
                                value="" 
                                class="regular-text" 
                                v-model="album_spotify" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="album_amazon">Amazon</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="album_amazon" 
                                value="" 
                                class="regular-text" 
                                v-model="album_amazon" 
                                :disabled="saving">
                        </td>
                    </tr>
                </table>
                <el-button 
                    size="small" 
                    type="success" 
                    icon="el-icon-circle-check" 
                    :disabled="!album_title || !album_artist || !album_artwork"
                    :loading="saving"
                    @click="saveNewAlbum">Save</el-button>
            </div>
        </div>
        <hr>
        <div class="album-search">
            <el-input 
                placeholder="Album or artist title..." 
                style="width: 20rem"
                size="small"
                clearable
                class="regular-text"
                v-model="search" 
                @change="searchAlbum" />
            <el-popover
                placement="top-start"
                title="Advanced Search"
                width="300"
                trigger="hover">
                <div>- Use syntax "album_name | artist_name" to search album by artist.</div>
                <div>- Ex: Superheroes | The Script </div>
                <i class="el-icon-question" slot="reference"></i>
            </el-popover>
        </div>
        <div class="album-list">
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
                                <img class="album-photo" v-lazy="item.artwork" alt="">
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
                                    filterable
                                    remote
                                    size="small"
                                    placeholder="Enter album name"
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
                                <input type="text" class="regular-text" v-model="item.artwork" :disabled="saving">
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
                                        @click="fetchAlbumThumb(item)">Fetch Thumb</el-button>
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
                                @click="edit !== item.id ? editAlbum(item) : saveAlbum(item)">
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
            album_title: '',
            album_artist: '',
            album_artwork: '',
            album_lastfm: '',
            album_youtube: '',
            album_spotify: '',
            album_amazon: '',
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
        searchAlbum() {
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

        fetchAlbumThumb(item) {
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
                alert('fetch album thumb error');
            })
            .finally(() => {
                this.fetching = false;
            });
        },

        editAlbum({id, artist, artist_name}) {
            if (this.fetching) {
                return alert('Fetching thumb! Please wait.')
            }

            if (this.saving) {
                return alert('Saving album! Please wait.')
            }

            this.album_artist = '';
            this.edit = id;

            Vue.set(this, 'options', [{
                id: artist,
                title: artist_name
            }]);
        },

        saveAlbum(item) {
            if (this.fetching) {
                return alert('Fetching thumb! Please wait.')
            }

            if (this.saving) {
                return alert('Saving album! Please wait.')
            }

            this.saving = true;
            ff_api.saveAlbum(item).then(res => {
                this.saving = false;
                this.edit = '0';
                this.success();
            })
            .catch(() => alert('save album error'))
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

            return ff_api.getListAlbum(params).then(res => {
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
            ff_api.fetchThumb(this.album_lastfm).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                this.album_artwork = res.thumb;
                this.album_title = res.name;
            })
            .catch(error => {
                alert('fetch album thumb error');
            })
            .finally(() => {
                this.fetching = false;
            });
        },

        saveNewAlbum() {
            this.saving = true;

            ff_api.saveNewAlbum({
                title: this.album_title,
                artist: this.album_artist,
                artwork: this.album_artwork,
                lastfm: this.album_lastfm,
                youtube: this.album_youtube,
                spotify: this.album_spotify,
                amazon: this.album_amazon,
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
            this.album_title = '';
            this.album_artist = '';
            this.album_artwork = '';
            this.album_lastfm = '';
            this.album_youtube = '';
            this.album_spotify = '';
            this.album_amazon = '';
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
.app-album {
    .new-album-container {
        margin-top: 1rem;
    }

    .new-album-thumb {
        width: 10rem;
    }

    .album-search {
        margin-top: 1rem;
    }

    .album-list {
        margin-top: 1rem;

        input {
            max-width: 20rem;
            width: 100%;
            margin: 0;
        }
    }

    .album-photo {
        max-width: 5rem;
        width: 100%;
    }

    .el-select input {
        background-color: #fff;
    }
}
</style>