import Vue from 'vue';
import VueLazyload from 'vue-lazyload';
import store from './store';
import App from './App.vue';

import { 
    Button, 
    Input, 
    Message, 
    MessageBox,
    Loading,
    Alert,
    Select,
    Option,
    Menu,
    MenuItem,
    Pagination,
    Tag,
    Popover,
    Dialog,
} from "element-ui";
import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';

document.addEventListener('DOMContentLoaded', () => {
    locale.use(lang)

    Vue.use(VueLazyload);
    Vue.use(Dialog)
    Vue.use(Popover);
    Vue.use(Tag);
    Vue.use(Pagination);
    Vue.use(Button);
    Vue.use(Input);
    Vue.use(Alert);
    Vue.use(Select);
    Vue.use(Option);
    Vue.use(Menu);
    Vue.use(MenuItem);
    Vue.use(Loading.directive);

    Vue.prototype.$loading = Loading.service;
    Vue.prototype.$msgbox = MessageBox;
    Vue.prototype.$alert = MessageBox.alert;
    Vue.prototype.$confirm = MessageBox.confirm;
    Vue.prototype.$prompt = MessageBox.prompt;
    Vue.prototype.$message = Message;

    new Vue({
        store,
        render: h => h(App)
    }).$mount('#ff-manager-app');
});