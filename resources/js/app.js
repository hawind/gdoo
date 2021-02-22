require('./bootstrap');

const Vue = require('vue');
window.Vue = Vue;

// 把vue的模块传递给全局变量
import Notification from './components/Notification.vue';
import draggable from 'vuedraggable';
import GdooFormDesigner from './components/GdooFormDesigner.vue';

window.GdooVueComponents = {
    draggable: draggable,
    gdooFormDesigner: GdooFormDesigner,
    notification: Notification
};
