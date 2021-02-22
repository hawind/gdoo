<template>
	<view class="tabs">
		
		<view class="header">
			<scroll-view id="tab-bar" class="scroll-h" :scroll-x="true" :show-scrollbar="false">
				<view v-for="(tab, index) in tabBars" :key="index" class="uni-tab-item" :id="tab.id" :data-current="index" @click="ontabtap">
					<text class="uni-tab-item-title" :class="tabIndex == index ? 'uni-tab-item-title-active' : ''">{{tab.name}}</text>
				</view>
			</scroll-view>
				
			<view class="search-controller">
				<uni-search-bar radius="50" placeholder="搜索主题或文号" @confirm="search" />
			</view>
		</view>
			
		<view class="line-h"></view>
		
		<swiper :current="tabIndex" class="swiper-box" style="flex:1;" :duration="300" @change="ontabchange">
		    <swiper-item class="swiper-item" v-for="(tab, index1) in newsList" :key="index1">
				<!-- #ifndef APP-NVUE -->
				<scroll-view class="scroll-v list" :show-scrollbar="true" refresher-enabled="true" enableBackToTop="true" scroll-y @scrolltolower="loadMore(index1)">
					<view v-for="(newsitem, index2) in tab.data" :key="index2">
						<media-item :options="newsitem" @click="goDetail(newsitem)"></media-item>
					</view>
					<view class="loading-more" v-if="tab.isLoading">
						<text class="loading-more-text">{{tab.loadingText}}</text>
					</view>
				</scroll-view>
				<!-- #endif -->
		    </swiper-item>
		</swiper>
	</view>
</template>

<script>
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
import mediaItem from '@/pages/app/workflow/item.nvue';
import {dateUtils} from '@/util.js';
export default {
	components: {
		uniLoadMore,
		mediaItem
	},
	data() {
		return {
			newsList: [],
			cacheTab: [],
			tabIndex: 0,
			tabBars: [{
			    name: '待办中',
			    id: 'todo'
			}, {
			    name: '已办理',
			    id: 'trans'
			}, {
			    name: '已结束',
			    id: 'done'
			}],
			
			page: 1,
			scrollInto: "",
			showTips: false,
			navigateFlag: false,
			pulling: false,
			refreshIcon: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAB5QTFRFcHBw3Nzct7e39vb2ycnJioqK7e3tpqam29vb////D8oK7wAAAAp0Uk5T////////////ALLMLM8AAABxSURBVHja7JVBDoAgDASrjqj//7CJBi90iyYeOHTPMwmFZrHjYyyFYYUy1bwUZqtJIYVxhf1a6u0R7iUvWsCcrEtwJHp8MwMdvh2amHduiZD3rpWId9+BgPd7Cc2LIkPyqvlQvKxKBJ//Qwq/CacAAwDUv0a0YuKhzgAAAABJRU5ErkJggg=="
		};
	},
	onLoad() {
		var me = this;
		me.tabBars.forEach((tabBar) => {
		    me.newsList.push({
		        data: [],
				page: 1,
		        isLoading: false,
				isComplete: false,
		        refreshText: "",
		        loadingText: '加载更多...'
		    });
		});
		this.getList(0);
	},
	methods: {
		loadMore(e) {
			this.getList(this.tabIndex);
		},
		ontabtap(e) {
			var me = this;
		    let index = e.target.dataset.current || e.currentTarget.dataset.current;
			// this.switchTab(index);
			me.tabIndex = index;
			//me.scrollInto = me.tabBars[index].id;
		},
		ontabchange(e) {
		    let index = e.target.current || e.detail.current;
		    this.switchTab(index);
		},
		switchTab(index) {
			var me = this;
		
		    me.getList(index);
			
			console.log(22);
			
			/*
		    // 缓存 tabId
		    if (this.newsList[this.tabIndex].data.length > MAX_CACHE_DATA) {
		        let isExist = this.cacheTab.indexOf(this.tabIndex);
		        if (isExist < 0) {
		            this.cacheTab.push(this.tabIndex);
		            //console.log("cache index:: " + this.tabIndex);
		        }
		    }
			*/
		   
		    me.tabIndex = index;
		    me.scrollInto = me.tabBars[index].id;
			/*
		    // 释放 tabId
		    if (this.cacheTab.length > MAX_CACHE_PAGE) {
		        let cacheIndex = this.cacheTab[0];
		        this.clearTabData(cacheIndex);
		        this.cacheTab.splice(0, 1);
		        //console.log("remove cache index:: " + cacheIndex);
		    }
			*/
		},
		clearTabData(e) {
		    this.newsList[e].data.length = 0;
		    this.newsList[e].loadingText = "加载更多...";
		},
		refreshData() {
			
		},
		onrefresh(e) {
		    var tab = this.newsList[this.tabIndex];
		    if (!tab.refreshFlag) {
		        return;
		    }
		    tab.refreshing = true;
		    tab.refreshText = "正在刷新...";
			
		    setTimeout(() => {
		        this.refreshData();
		        this.pulling = true;
		        tab.refreshing = false;
				tab.refreshFlag = false;
		        tab.refreshText = "已刷新";
		        setTimeout(() => { // TODO fix ios和Android 动画时间相反问题
		            this.pulling = false;
		        }, 500);
				
		    }, 2000);
		},
		onpullingdown(e) {
		    var tab = this.newsList[this.tabIndex];
		    if (tab.refreshing || this.pulling) {
		        return;
		    }
		    if (Math.abs(e.pullingDistance) > Math.abs(e.viewHeight)) {
		        tab.refreshFlag = true;
		        tab.refreshText = "释放立即刷新";
		    } else {
		        tab.refreshFlag = false;
		        tab.refreshText = "下拉可以刷新";
		    }
		},
		formatDate(value) {
			return dateUtils.formatDate(value);
		},
		getList(index) {
			var me = this;
			let tab = me.newsList[index];
			let option = me.tabBars[index].id;

			if (tab.isComplete) {
				return;
			}
			
			me.$api.post('workflow/workflow/index', {option: option, page: tab.page}).then(res => {
				
				tab.data = tab.data.concat(res.data);
				
				uni.stopPullDownRefresh();
				
				if (res.current_page >= res.last_page) {
					tab.isComplete = true;
				} else {
					tab.page = res.current_page + 1;
				}
				
			}).catch(error => {
			});
		},
		goDetail: function(e) {
			// 				if (!/前|刚刚/.test(e.published_at)) {
			// 					e.published_at = dateUtils.format(e.published_at);
			// 				}
			let detail = {
				author_name: e.author_name,
				cover: e.cover,
				id: e.id,
				post_id: e.post_id,
				published_at: e.published_at,
				title: e.title
			};
			uni.navigateTo({
				url: '../list2detail-detail/list2detail-detail?detailDate=' + encodeURIComponent(JSON.stringify(detail))
			});
		},
		setTime: function(items) {
			var newItems = [];
			items.forEach(e => {
				newItems.push({
					author_name: e.author_name,
					cover: e.cover,
					id: e.id,
					post_id: e.post_id,
					published_at: dateUtils.format(e.published_at),
					title: e.title
				});
			});
			return newItems;
		}
	}
};
</script>

<style>
.uni-media-list-logo {
	width: 140rpx;
	height: 140rpx;
}

.uni-media-list-body {
	height: auto;
	justify-content: space-around;
}

.uni-media-list-text-top {
	height: 50rpx;
	font-size: 28rpx;
	overflow: hidden;
}

.uni-media-list-text-bottom {
	/*
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	*/
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
        /* #ifndef MP-ALIPAY */
        flex-direction: column;
        /* #endif */
        width: 750rpx;
    }

    .update-tips {
        position: absolute;
        left: 0;
        top: 41px;
        right: 0;
        padding-top: 5px;
        padding-bottom: 5px;
        background-color: #FDDD9B;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .update-tips-text {
        font-size: 14px;
        color: #ffffff;
    }

    .refresh {
        width: 750rpx;
        height: 64px;
        justify-content: center;
    }

    .refresh-view {
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: center;
    }

	.refresh-icon {
		width: 30px;
		height: 30px;
		transition-duration: .5s;
		transition-property: transform;
		transform: rotate(0deg);
		transform-origin: 15px 15px;
	}

	.refresh-icon-active {
		transform: rotate(180deg);
	}

	.loading-icon {
		width: 20px;
		height: 20px;
		margin-right: 5px;
		color: #999999;
	}

    .loading-text {
        margin-left: 2px;
        font-size: 16px;
        color: #999999;
    }

    .loading-more {
        align-items: center;
        justify-content: center;
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: center;
    }

    .loading-more-text {
        font-size: 28rpx;
        color: #999;
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
