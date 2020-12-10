<template>
    <div class="app-chart-data">
        <div class="chart-toolbar">
            <el-button 
                size="small" 
                type="success" 
                icon="el-icon-circle-check" 
                :disabled="!title.trim() || loading"
                @click="saveChartData">Save</el-button>
            <el-button 
                size="small" 
                icon="el-icon-circle-close" 
                :disabled="loading"
                @click="$emit('close-chart-data')">Close</el-button>
        </div>
        <hr>
        <div class="app-chart-data-container" v-loading="loading">
            <el-tag type="success" effect="dark" v-if="type === 'album'">{{type}}</el-tag>
            <el-tag type="primary" effect="dark" v-if="type === 'artist'">{{type}}</el-tag>
            <el-tag type="warning" effect="dark" v-if="type === 'song'">{{type}}</el-tag>
            <el-input 
                style="width: 20rem"
                size="small"
                clearable
                placeholder="Chart name" 
                type="text" 
                class="regular-text"
                v-model="title" />
            <el-button 
                size="small" 
                icon="el-icon-plus" 
                @click="dialog = true">Add Item</el-button>
            <el-button 
                plain
                type="danger"
                size="small" 
                icon="el-icon-plus" 
                @click="deleteAllChartItems">Delete All Items</el-button>

            <el-dialog
                width="50%"
                :visible.sync="dialog"
                >
                <span class="el-dialog__title" slot="title">
                    Search {{type}}
                    <el-popover
                        placement="right-start"
                        title="Advanced Search"
                        width="300"
                        trigger="hover">
                        <div>- Use syntax "title | artist_name" to search item by artist. Ex: Dynamite | BTS </div>
                        <div>- Search result only show 20 items. Put more details for getting better results.</div>
                        <i class="el-icon-question" slot="reference"></i>
                    </el-popover>
                </span>
                <div>
                    <el-input 
                        size="small"
                        clearable
                        placeholder="string to search" 
                        type="text" 
                        class="regular-text"
                        v-model="search"
                        @change="searchItem" />
                    <br>
                    <br>
                    <table class="list-item wp-list-table widefat fixed striped table-view-list posts" v-loading="searching">
                        <thead>
                            <th style="width: 5rem;">Photo</th>
                            <th>Title</th>
                            <th style="width: 5rem;"></th>
                        </thead>
                        <tbody>
                            <tr v-for="item in searchItems" :key="item.id">
                                <td>
                                    <a target="_blank" :href="item.artwork">
                                        <img class="artist-photo" v-lazy="item.artwork" alt="">
                                    </a>
                                </td>
                                <th>
                                    <div><strong>{{item.title}}{{item.artist_name ? '— ['+ item.artist_name +']' : ''}}</strong></div>
                                </th>
                                <th>
                                    <el-button 
                                        size="small" 
                                        icon="el-icon-plus" 
                                        v-if="!isAdded(item.id)"
                                        @click="addItem(item)">Add</el-button>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </el-dialog>
            <table class="list-item chart-items wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                    <th style="width: 3rem"></th>
                    <th style="width: 5rem;">Position</th>
                    <th style="width: 5rem;">Photo</th>
                    <th>Title</th>
                    <th class="th-center">Last</th>
                    <th class="th-center">Peak</th>
                    <th class="th-center">Total</th>
                    <th class="th-center"></th>
                </thead>
                <draggable v-model="list" tag="tbody">
                    <tr v-for="(item, idx) in list" :key="item.item_id">
                        <th class="th-handler"><i class="el-icon-rank"></i></th>
                        <th><strong>{{idx + 1}}</strong></th>
                        <th>
                            <a target="_blank" :href="item.artwork">
                                <img class="artist-photo" v-lazy="item.artwork" alt="">
                            </a>
                        </th>
                        <th>
                            <div><strong>{{item.title}}</strong></div>
                        </th>
                        <th class="th-center">
                            <input type="number" size="small" v-model="item.last" />
                        </th>
                        <th class="th-center">
                            <input type="number" size="small" v-model="item.peak" />
                        </th>
                        <th class="th-center">
                            <input type="number" size="small" v-model="item.total" />
                        </th>
                        <th class="th-center">
                            <el-button type="danger" size="small" plain @click="removeItem(item)">remove</el-button>
                        </th>
                    </tr>
                </draggable>
            </table>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import draggable from 'vuedraggable'

export default {
    components: {
        draggable,
    },

    props: {
        chart: {
            type: Object,
            default() {
                return {
                    title: 'test',
                    id: '31',
                    type: 'song'
                };
            }
        },
    },
    
    data() {
        return {
            saving: '',
            title: '',
            type: 'album',
            list: [],
            dialog: false,
            search: '',
            searchItems: [],
            searching: false,
            loading: true,
        }
    },

    mounted() {
        this.title = this.chart.title;
        this.type = this.chart.type;
        this.getChartData();
    },

    methods: {
        deleteAllChartItems() {
            const ok = confirm('Are you sure?');
            if (!ok) {
                return;
            }

            this.loading = true;
            ff_api.deleteAllChartItems(this.chart.id).then(() => {
                this.getChartData();
            })
            .catch(error => {
                console.error('delete all items error');
            })
            .finally(() => {
                this.loading = false;
            })
        },

        saveChartData() {
            this.loading = true;

            ff_api.saveChartData({
                chart_id: this.chart.id,
                chart_title: this.title,
                data: this.list,
            })
            .then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                return this.getChartData();
            })
            .finally(() => {
                this.success();
            })
        },

        getChartData() {
            ff_api.getChartData(this.chart.id).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                if (res.list) {
                    Vue.set(this, 'list', res.list);
                }
            })
            .catch(error => {
                console.error('get chart data error')
            })
            .finally(() => {
                this.loading = false;
            });
        },

        removeItem({chart_id, item_id}) {
            const ok = confirm('Are you sure?');
            if (!ok) {
                return;
            }

            this.loading = true;
            ff_api.removeChartItem({chart_id, item_id}).then(() => {
                this.getChartData();
            })
            .catch(error => {
                console.error('remove item error');
            })
            .finally(() => {
                this.loading = false;
            })
        },
        
        isAdded(id) {
            return !!this.list.find(item => item.item_id === id);
        },

        addItem(item) {
            if (item.artist_name) {
                item.title = item.title + ' — [' + item.artist_name + ']';
            }

            if (this.isAdded(item.id)) {
                return;
            }

            this.list.push({
                item_id: item.id,
                title: item.title,
                artwork: item.artwork,
                last: '0',
                peak: '0',
                total: '1',
                chart_id: this.chart.id,
            });
        },

        searchItem() {
            if (!this.search.trim()) {
                return Vue.set(this, 'searchItems', []);
            }

            this.searching = true;

            const params = {
                search: this.search.trim(),
                page: 1
            };

            this.searchApi(params).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                Vue.set(this, 'searchItems', res.list);
                this.searching = false;
            })
        },

        searchApi(params) {
            if (this.type === 'album') {
                return ff_api.getListAlbum(params);
            }

            if (this.type === 'song') {
                return ff_api.getListSong(params);
            }

            if (this.type === 'artist') {
                return ff_api.getListArtist(params);
            }
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
.app-chart-data {
    .chart-list {
        margin-top: 1rem;

        input {
            max-width: 20rem;
            width: 100%;
            margin: 0;
        }
    }

    .list-item {
        margin-top: 1rem;

        img {
            max-width: 5rem;
            width: 100%;
        }
    }

    .app-chart-data-container {
        padding-top: 1rem;
    }

    .chart-items {
        .th-center {
            width: 7rem;
            text-align: center;

            input {
                width: 3rem;
                text-align: center;
            }
        }

        .th-handler {
            font-size: 1.2rem;
            text-align: center;
            cursor: move;
        }

        .sortable-ghost {
            opacity: 0.5;
            background: #c8ebfb;
        }
    }
}
</style>