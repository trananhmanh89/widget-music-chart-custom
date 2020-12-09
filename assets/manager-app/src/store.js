import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    strict: true,

    state: {
        activeMenu: 'chart',
        loading: true,
    },

    actions: {
        
    },

    mutations: {
        activeMenu(state, value) {
            state.activeMenu = value;
        },

        loading(state, value) {
            state.loading = value;
        }
    },
})