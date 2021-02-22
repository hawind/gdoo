<template>
	<view>
		<view class="header" id="header">
			<scroll-view id="tab-bar" class="scroll-h" :scroll-x="true" :show-scrollbar="false">
				<view v-for="(tab, index) in tabBars" :key="index" class="uni-tab-item" :id="tab.id" @click="ontabtap(index)">
					<text class="uni-tab-item-title" :class="tabIndex == index ? 'uni-tab-item-title-active' : ''">{{tab.name}}</text>
				</view>
			</scroll-view>
				
			<view class="search-controller">
				<uni-search-bar radius="50" placeholder="搜索主题或文号" @confirm="search" />
			</view>
			<view class="line-h"></view>
		</view>
		
		<view class="uni-list uni-list-controller" :style="'margin-top:' + listTop + 'px;'">
			<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-for="(row, key) in tabBars[tabIndex].data" :key="key" @click="goDetail(row)">
				<view class="uni-media-list">
					<view class="uni-media-list-body">
						<view class="uni-media-list-text-top">{{ row.title }}</view>
						<view class="uni-media-list-text-bottom">
							<text class="desc">{{ row.name }}</text>
							<text class="desc">{{ row.step.name }}</text>
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
export default {
	components: {
		uniLoadMore
	},
	data() {
		return {
			tabIndex: 0,
			listTop: 103,
			tabBars: [{
			    name: '待办中',
			    id: 'todo',
				page: 1,
				data: [],
				status: 'more',
				contentText: {
					contentdown: '上拉加载更多',
					contentrefresh: '加载中',
					contentnomore: '没有更多'
				}
			}, {
			    name: '已办理',
			    id: 'trans',
				page: 1,
				data: [],
				status: 'more',
				contentText: {
					contentdown: '上拉加载更多',
					contentrefresh: '加载中',
					contentnomore: '没有更多'
				}
			}, {
			    name: '已结束',
			    id: 'done',
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
	onLoad() {
		var me = this;
		me.getList(0);
	},
	mounted() {
		let me = this;
		uni.createSelectorQuery().in(this).select('#header').boundingClientRect(function(e) {
			me.listTop = e.height - 1;
		}).exec();
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
	
	methods: {
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
		
			me.$api.post('workflow/workflow/index', {option: tab.id, page: tab.page}).then(res => {
				
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
		},
		goDetail: function(row) {
			uni.navigateTo({
				url: '/pages/webview?title=流程详情&url=' + encodeURIComponent('workflow/workflow/edit?process_id=' + row.process_id),
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
	width: 100vw;
}
	
.uni-media-list-logo {
	width: 140rpx;
	height: 140rpx;
}

.uni-media-list-body {
	height: auto;
	justify-content: space-around;
}

.uni-media-list-text-top {
	font-size: 28rpx;
	overflow: hidden;
}

.uni-media-list-text-bottom {
	/*
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	*/
	margin-top: 15rpx;
}

.uni-media-list-text-bottom .desc {
	margin-bottom: 15rpx;
	display: block;
}
.uni-media-list-text-bottom .desc:last-child {
	margin-bottom: 0;
}

.tabs {
        flex: 1;
        flex-direction: column;
        overflow: hidden;
        background-color: #ffffff;
        height: 100vh;
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
		width: 33.33333%;
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

    .swiper-box {
        flex: 1;
		height: 50vh;
    }

    .swiper-item {
        flex: 1;
        flex-direction: row;
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
