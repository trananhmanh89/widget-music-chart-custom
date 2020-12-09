<template>
    <div id="ff-manager-app">
        <app-nav></app-nav>
        <div class="app-container" v-loading="loading">
            <app-chart v-if="menu === 'chart'"></app-chart>
            <app-song v-if="menu === 'song'"></app-song>
            <app-artist v-if="menu === 'artist'"></app-artist>
            <app-album v-if="menu === 'album'"></app-album>
        </div>
    </div>
</template>

<script>
import AppNav from './components/AppNav.vue';
import AppChart from './components/AppChart.vue';
import AppArtist from './components/AppArtist.vue';
import AppSong from './components/AppSong.vue';
import AppAlbum from './components/AppAlbum.vue';

export default {
    components: {
        AppNav,
        AppChart,
        AppArtist,
        AppSong,
        AppAlbum,
    },

    data() {
        return {
            input: ''
        }
    },

    computed: {
        menu() {
            return this.$store.state.activeMenu;
        },

        loading() {
            return this.$store.state.loading;
        }
    }
}
</script>

<style lang="scss">
body {
    overflow-y: scroll;;
}

#ff-manager-app {
    width: 99%;

    .app-container {
        padding: 2rem 1rem;
    }
}

.el-popover {
    word-break: normal;
    text-align: unset;
}
</style>