<template>
    <div class="app-artist">
        <div class="artist-toolbar">
            <el-button size="small" icon="el-icon-plus" @click="show = !show">New Artist</el-button>
            <div class="new-artist-container" v-if="show">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="artist_name">Artist Name *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="artist_name" 
                                value="" 
                                class="regular-text" 
                                v-model="artist_name" 
                                :disabled="saving">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="last_fm_link">Last.fm Link *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="last_fm_link" 
                                value="" 
                                class="regular-text" 
                                v-model="artist_link" 
                                :disabled="saving">
                            <el-button 
                                size="small" 
                                icon="el-icon-circle-check" 
                                :disabled="!artist_link || saving"
                                :loading="fetching"
                                @click="fetchThumb">Fetch Thumb</el-button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="artist_thumb">Photo *</label>
                        </th>
                        <td>
                            <input 
                                type="text" 
                                id="artist_thumb" 
                                value="" 
                                class="regular-text" 
                                v-model="artist_thumb" 
                                :disabled="saving">
                            <br>
                            <br>
                            <img v-if="artist_thumb"  class="new-artist-thumb" :src="artist_thumb" alt="">
                        </td>
                    </tr>
                </table>
                <el-button 
                    size="small" 
                    type="success" 
                    icon="el-icon-circle-check" 
                    :disabled="!artist_thumb || !artist_link || !artist_name"
                    :loading="saving"
                    @click="saveNewArtist">Save</el-button>
            </div>
        </div>
        <hr>
        <div class="artist-search">
            <el-input 
                placeholder="Name of artist" 
                style="width: 20rem"
                size="small"
                clearable
                class="regular-text"
                v-model="search" 
                @change="searchArtist" />
        </div>
        <div class="artist-list">
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                    <th width="10%">Photo</th>
                    <th>Name</th>
                    <th>Last.fm</th>
                    <th width="5%">ID</th>
                    <th width="10%">Action</th>
                </thead>
                <tbody>
                    <tr v-for="item in list" :key="item.id">
                        <td>
                            <a target="_blank" :href="item.artwork">
                                <img class="artist-photo" v-lazy="item.artwork" alt="">
                            </a>
                        </td>
                        <th>
                            <div v-if="edit !== item.id"><strong>{{item.title}}</strong></div>
                            <div v-if="edit === item.id">
                                <input class="regular-text" type="text" v-model="item.title" :disabled="saving">
                                <br>
                                <br>
                                <div>Artwork</div>
                                <input class="regular-text" type="text" v-model="item.artwork" :disabled="saving">
                            </div>
                        </th>
                        <th>
                            <div v-if="edit !== item.id">
                                <a target="_blank" :href="item.link">{{item.link}}</a>
                            </div>
                            <div style="display: flex" v-if="edit === item.id">
                                <input class="regular-text" type="text" v-model="item.link" :disabled="saving">
                                <el-button 
                                    size="small" 
                                    icon="el-icon-circle-check" 
                                    :loading="fetching"
                                    :disabled="saving"
                                    @click="fetchArtistThumb(item)">Fetch Thumb</el-button>
                            </div>
                        </th>
                        <th><strong>{{item.id}}</strong></th>
                        <th>
                            <el-button 
                                size="small" 
                                icon="el-icon-circle-check" 
                                :loading="edit === item.id && saving"
                                @click="edit !== item.id ? editArtist(item) : saveArtist(item)">
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
            show: false,
            artist_name: '',
            artist_link: '',
            artist_thumb: '',
            fetching: false,
            saving: false,
            search: '',
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
        searchArtist() {
            this.page = 1;
            this.getList();
        },
        onPageChange(num) {
            this.page = num;
            this.getList().then(() => {
                document.documentElement.scrollTop = 0
            });
        },

        fetchArtistThumb(item) {
            this.fetching = true;
            const {id, link} = item;
            ff_api.fetchThumb(link).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                item.artwork = res.thumb;
                item.title = res.name;

                this.fetching = false;
            })
            .catch(error => {
                alert('fetch artist thumb error');
            })
            .finally(() => {
                this.fetching = false;
            });
        },

        editArtist({id, title, link, artwork}) {
            if (this.fetching) {
                return alert('Fetching thumb! Please wait.')
            }

            if (this.saving) {
                return alert('Saving Artist! Please wait.')
            }

            this.edit = id;
        },

        saveArtist(item) {
            if (this.fetching) {
                return alert('Fetching thumb! Please wait.')
            }

            if (this.saving) {
                return alert('Saving Artist! Please wait.')
            }

            this.saving = true;
            ff_api.saveArtist(item).then(res => {
                this.saving = false;
                this.edit = '0';
                this.$message({
                    message: 'success',
                    type: 'success',
                    offset: 50,
                })
            })
            .catch(() => alert('save artist error'))
        },

        getList() {
            const params = {
                search: this.search.trim(),
                page: this.page
            };

            return ff_api.getListArtist(params).then(res => {
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
            ff_api.fetchThumb(this.artist_link).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                this.artist_thumb = res.thumb;
                this.artist_name = res.name;
            })
            .finally(() => {
                this.fetching = false;
            });
        },

        saveNewArtist() {
            this.saving = true;

            ff_api.saveNewArtist({
                title: this.artist_name,
                link: this.artist_link,
                thumb: this.artist_thumb
            })
            .then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                if (res.success) {
                    this.$message({
                        message: 'success',
                        type: 'success',
                        offset: 50,
                    })

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
            this.artist_name = '';
            this.artist_link = '';
            this.artist_thumb = '';
            this.edit = 0;
        }
    }
}
</script>

<style lang="scss">
.app-artist {
    .new-artist-container {
        margin-top: 1rem;
    }

    .new-artist-thumb {
        width: 10rem;
    }

    .artist-search {
        margin-top: 1rem;
    }

    .artist-list {
        margin-top: 1rem;

        input {
            max-width: 20rem;
            width: 100%;
            margin: 0;
        }
    }

    .artist-photo {
        max-width: 5rem;
        width: 100%;
    }
}
</style>