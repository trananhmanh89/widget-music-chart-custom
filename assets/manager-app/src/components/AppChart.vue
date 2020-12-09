<template>
    <div class="app-chart">
        <div v-if="chart_id === '0'">
            <div class="chart-toolbar">
                <el-button size="small" icon="el-icon-plus" @click="show = !show">New Chart</el-button>
                <div class="new-chart-container" v-if="show">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="chart_title">Chart Title *</label>
                            </th>
                            <td>
                                <input 
                                    type="text" 
                                    id="chart_title" 
                                    value="" 
                                    class="regular-text" 
                                    v-model="chart_title" 
                                    :disabled="saving">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Chart Type *</label>
                            </th>
                            <td>
                                <el-select size="small" v-model="chart_type">
                                    <el-option :label="'Album'" :value="'album'"></el-option>
                                    <el-option :label="'Artist'" :value="'artist'"></el-option>
                                    <el-option :label="'Song'" :value="'song'"></el-option>
                                </el-select>
                            </td>
                        </tr>
                    </table>
                    <el-button 
                        size="small" 
                        type="success" 
                        icon="el-icon-circle-check" 
                        :disabled="!chart_title"
                        :loading="saving"
                        @click="saveNewChart">Save</el-button>
                </div>
            </div>
            <hr>
            <div class="chart-search">
                <el-input 
                    style="width: 20rem"
                    size="small"
                    clearable
                    placeholder="Chart name" 
                    type="text" 
                    class="regular-text"
                    v-model="search" 
                    @change="searchChart" />
                <el-select size="small" v-model="filter_type" @change="searchChart">
                    <el-option :label="'-- All --'" :value="''"></el-option>
                    <el-option :label="'Album'" :value="'album'"></el-option>
                    <el-option :label="'Artist'" :value="'artist'"></el-option>
                    <el-option :label="'Song'" :value="'song'"></el-option>
                </el-select>
            </div>
            <div class="chart-list" v-loading="loading">
                <table class="wp-list-table widefat fixed striped table-view-list posts">
                    <thead>
                        <th>Title</th>
                        <th>Type</th>
                        <th style="width: 7rem;"></th>
                    </thead>
                    <tbody>
                        <tr v-for="item in list" :key="item.id">
                            <th>
                                <div v-if="edit !== item.id">
                                    <a href="javascript:;" @click="openChartData(item.id)">{{item.title}}</a>
                                </div>
                                <div v-if="edit === item.id">
                                    <input class="regular-text" type="text" v-model="item.title" :disabled="saving">
                                </div>
                            </th>
                            <th>
                                <el-tag type="success" v-if="item.type === 'album'">{{item.type}}</el-tag>
                                <el-tag type="primary" v-if="item.type === 'artist'">{{item.type}}</el-tag>
                                <el-tag type="warning" v-if="item.type === 'song'">{{item.type}}</el-tag>
                            </th>
                            <th>
                                <el-button 
                                    plain
                                    size="small" 
                                    type="danger"
                                    icon="el-icon-circle-check"
                                    @click="deleteChart(item.id)" >
                                    delete
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
    <app-chart-data 
        v-if="chart_id !== '0'" 
        :chart="currentChart"
        @close-chart-data="onCloseChartData"/>
    </div>
</template>

<script>
import Vue from 'vue';
import AppChartData from './AppChartData.vue';

export default {
    components: {
        AppChartData,
    },

    data() {
        return {
            show: false,
            chart_title: '',
            chart_type: 'album',
            filter_type: '',
            saving: false,
            search: '',
            page: 1,
            page_size: 20,
            total: 0,
            list: [],
            edit: '0',
            chart_id: '0',
            loading: false,
        }
    },

    mounted() {
        if (this.chart_id === '0') {
            this.getList();
        }
    },

    computed: {
        currentChart() {
            return this.list.find(chart => chart.id === this.chart_id);
        }
    },

    methods: {
        deleteChart(chart_id) {
            const ok = confirm('Are you sure?');
            if (!ok) {
                return;
            }

            this.loading = true;
            ff_api.deleteChart(chart_id).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                return this.getList().then(() => {
                    this.loading = false;
                });
            })
        },

        onCloseChartData() {
            this.chart_id = '0';
            this.getList();
        },

        openChartData(id) {
            this.chart_id = id;
        },

        searchChart() {
            this.page = 1;
            this.getList();
        },

        onPageChange(num) {
            this.page = num;
            this.getList().then(() => {
                document.documentElement.scrollTop = 0
            });
        },

        getList() {
            const params = {
                search: this.search.trim(),
                page: this.page,
                type: this.filter_type,
            };

            return ff_api.getListChart(params).then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                Vue.set(this, 'list', res.list);
                this.total = +res.total;
                this.$store.commit('loading', false);
            });
        },

        saveNewChart() {
            this.saving = true;

            ff_api.saveNewChart({
                title: this.chart_title,
                type: this.chart_type,
            })
            .then(res => {
                if (res.error) {
                    return alert(res.error);
                }

                if (res.success) {
                    return this.getList().then(() => {
                        this.success();
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
            this.chart_title = '';
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
.app-chart {
    .new-chart-container {
        margin-top: 1rem;
    }

    .chart-search {
        margin-top: 1rem;

        .el-select input {
            background-color: #fff;
        }
    }

    .chart-list {
        margin-top: 1rem;

        table tr:hover {
            background-color: #f0f8ff;
        }

        input {
            max-width: 20rem;
            width: 100%;
            margin: 0;
        }
    }
}
</style>