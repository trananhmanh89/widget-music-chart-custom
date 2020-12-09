<template>
    <div class="app-menu">
        <el-menu :default-active="active" class="el-menu-demo" mode="horizontal" @select="handleSelect">
            <el-menu-item index="chart">Chart</el-menu-item>
            <el-menu-item index="album">Album</el-menu-item>
            <el-menu-item index="song">Song</el-menu-item>
            <el-menu-item index="artist">Artist</el-menu-item>
        </el-menu>
    </div>
</template>

<script>
export default {
    data() {
        return {

        }
    },

    computed: {
        active() {
            return this.$store.state.activeMenu;
        }
    },

    methods: {
        handleSelect(idx) {
            if (this.active === idx) {
                return;
            }
            this.$store.commit('loading', true);
            this.$store.commit('activeMenu', idx);
        }
    }
}
</script>

<style>

</style>