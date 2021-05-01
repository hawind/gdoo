require('./bootstrap');

const Vue = require('vue');
window.Vue = Vue;

// 第三方全局模块
import draggable from 'vuedraggable';
draggable.name = 'gdoo-draggable';
window.gdooDraggable = draggable;

// 自定义全局模块
import gdooFrameHeader from './components/gdooFrameHeader.vue';
import gdooFormDesigner from './components/gdooFormDesigner.vue';
import gdooGridHeader from './components/gdooGridHeader.vue';
window.gdooFrameHeader = gdooFrameHeader;
window.gdooFormDesigner = gdooFormDesigner;
window.gdooGridHeader = gdooGridHeader;