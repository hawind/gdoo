<template>
	<view>
		<view class="header" id="header">
			<uni-nav-bar color="#ffffff" background-color="#007AFF" status-bar="true" left-icon="arrowleft" left-text="返回" :right-text="is_add ? '新增' : ''" @clickLeft="back" @clickRight="add" />
			<scroll-view id="tab-bar" class="scroll-h" :scroll-x="true" :show-scrollbar="false">
				<view v-for="(tab, index) in tabBars" :key="index" class="uni-tab-item" :id="tab.id" @click="ontabtap(index)">
					<text class="uni-tab-item-title" :class="tabIndex == index ? 'uni-tab-item-title-active' : ''">{{tab.name}}</text>
				</view>
			</scroll-view>
		</view>

		<view class="uni-list uni-list-controller" :style="'margin-top:' + listTop + 'px;'">
			<view v-for="(row, key) in tabBars[tabIndex].data" :key="key">
				<view class="uni-media-list">
					<view class="uni-media-list-body">
						<img :src=" baseURL + '/uploads/' + row.path" style="width:100%;">
						<view class="uni-media-list-text">
							<view class="uni-media-list-text-top">
								<text>{{ row.created_by }}</text>
								<text>{{ formatDate(row.created_at) }}</text>
							</view>
							<view class="uni-media-list-text-bottom">
								<text>{{ row.location }}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<uni-load-more :status="tabBars[tabIndex].status" :icon-size="16" :content-text="tabBars[tabIndex].contentText" />
	</view>
</template>

<script>
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
import uniNavBar from '@/components/uni-nav-bar/uni-nav-bar.vue'
import {dateUtils} from '@/util.js';
import {baseURL} from '@/api.js';
export default {
	components: {
		uniLoadMore,
		uniNavBar
	},
	data() {
		return {
			is_add: false,
			promotion_id: 0,
			tabIndex: 0,
			listTop: 103,
			baseURL: baseURL,
			tabBars: [{
			    name: '审核中',
			    id: '0',
				page: 1,
				data: [],
				status: 'more',
				contentText: {
					contentdown: '上拉加载更多',
					contentrefresh: '加载中',
					contentnomore: '没有更多'
				}
			}, {
			    name: '已审核不合格',
			    id: '1',
				page: 1,
				data: [],
				status: 'more',
				contentText: {
					contentdown: '上拉加载更多',
					contentrefresh: '加载中',
					contentnomore: '没有更多'
				}
			}, {
			    name: '已审核合格',
			    id: '2',
				page: 1,
				data: [],
				status: 'more',
				contentText: {
					contentdown: '上拉加载更多',
					contentrefresh: '加载中',
					contentnomore: '没有更多'
				}
			}],
			reload: false
		};
	},
	onLoad(options) {
		var me = this;
		me.promotion_id = options.promotion_id;
		
		// 判断谁可以上传资料
		var user = uni.getStorageSync('user');
		if (user.group_id == 3 || user.id == 1) {
			me.is_add = true;
		}
		
		me.getList(0);
	},
	onPullDownRefresh() {
		let me = this;
		me.reload = true;
		
		let tab = me.tabBars[me.tabIndex];
		tab.page = 1;
		tab.status = 'more';
		me.getList(me.tabIndex);
	},
	onReachBottom() {
		let me = this;
		let tab = me.tabBars[me.tabIndex];
		if (tab.status == 'noMore') {
			return;
		}
		tab.status = 'more';
		this.getList(me.tabIndex);
	},
	mounted() {
		let me = this;
		uni.createSelectorQuery().in(this).select('#header').boundingClientRect(function(e) {
			me.listTop = e.height - 1;
		}).exec();
	},
	methods: {
		back() {
			uni.navigateBack();
		},
		add() {
			let me = this;
			uni.navigateTo({
				url: '/pages/app/promotionMaterial/upload?promotion_id=' + me.promotion_id
			});
		},
		ontabtap(index) {
			var me = this;
			me.tabIndex = index;
			this.switchTab(index);
		},
		switchTab(index) {
			var me = this;
			let tab = me.tabBars[index];
			if (tab.data.length == 0) {
				me.getList(index);
			}
		},
        htmlClear(str) {
            // 正则去掉所有的html标记
            return str.replace(/<[^>]+>/g, "");
        },
		formatDate(value) {
			return dateUtils.formatDate(value);
		},
		getList(index) {
			var me = this;
			let tab = me.tabBars[index];
			
			if (tab.status == 'noMore') {
				if (me.reload) {
					me.reload = false;
					uni.stopPullDownRefresh();
				}
				return;
			}
			
			me.$api.post('promotion/material/index', {by: tab.id, page: tab.page}).then(res => {
				
				if (me.reload) {
					tab.data = [];
					me.reload = false;
					uni.stopPullDownRefresh();
				}
				
				tab.data = tab.data.concat(res.data);
	
				if (res.current_page >= res.last_page) {
					tab.status = 'noMore';
				} else {
					tab.page = res.current_page + 1;
				}
				
			}).catch(error => {
			});
		}
	}
};
</script>

<style>
page {
	width: 100%;
	height: 100%;
	display: flex;
	flex-wrap: wrap;
	align-items: flex-start;
	justify-content: center;
	background: rgba(249, 249, 249, 1);
}
.header {
	position: fixed;
	top: 0;
	right: 0;
	left: 0;
	z-index: 100;
	background: #fff;
	width: 100vw;
}

.uni-list-controller {
	margin-top: 103px;
	background-color: #f1f1f1;
	width: 100vw;
}
	
.uni-media-list {
}
	
.uni-media-list-logo {
	width: 140rpx;
	height: 140rpx;
}

.uni-media-list-body {
	background-color: #fff;
	height: auto;
	justify-content: space-around;
	border-radius: 10rpx;
}

.uni-media-list-text {
	width: calc(100% - 30rpx);
	padding: 15rpx;
}
.uni-media-list-text-top {
    display: flex;
	font-size: 28rpx;
    flex-direction: row;
    justify-content: space-between;
	padding-bottom: 0;
}

.uni-media-list-text-bottom {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	padding-bottom: 15rpx;
}

.scroll-h {
	width: 750rpx;
	height: 80rpx;
	flex-direction: row;
	white-space: nowrap;
	align-items: center;
	border-bottom: 1px solid #c8c7cc;
}

.line-h {
	height: 1rpx;
	background-color: #cccccc;
}

.uni-tab-item {
	display: inline-block;
	/*
	padding-left: 70rpx;
	padding-right: 70rpx;
	*/
	text-align: center;
	width: 33.333333%;
}

.uni-tab-item-title {
	color: #555;
	font-size: 30rpx;
	height: 80rpx;
	line-height: 80rpx;
	flex-wrap: nowrap;
	white-space: nowrap;
}

.uni-tab-item-title-active {
	color: #007AFF;
}

.scroll-v {
	flex: 1;
	width: 750rpx;
}

.search-controller {
	padding: 10rpx;
}
	
.search-result {
	margin-top: 10px;
	margin-bottom: 20px;
	text-align: center;
}
	
.search-result-text {
	text-align: center;
	font-size: 14px;
}

</style>
