<template>
	<view>
		<view class="header" id="header">
			<view class="search-controller">
				<uni-search-bar radius="50" placeholder="搜索单号" @confirm="search" />
			</view>
			<view class="line-h"></view>
		</view>
		<view v-if="rows.length > 0">
			<view class="uni-list uni-list-controller" :style="'margin-top:' + listTop + 'px;'">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-for="(row, key) in rows" :key="key" @click="goDetail(row)">
					<view class="uni-media-list">
						<view class="uni-media-list-body">
							<view class="uni-media-list-text-top">
		                        <text>{{ row.sn }}</text>
		                        <text>{{ row.name }}</text>
		                    </view>
							<view class="uni-media-list-text-bottom">
		                        <text>{{ row.run_name }}</text>
								<text>{{ formatDate(row.created_at) }}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<!--
			<uni-load-more :status="status" :icon-size="16" :content-text="contentText" />
			-->
		</view>
		<view v-else style="padding-top:300rpx;">
			<img alt="暂无数据" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNDEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAxKSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgIDxlbGxpcHNlIGZpbGw9IiNGNUY1RjUiIGN4PSIzMiIgY3k9IjMzIiByeD0iMzIiIHJ5PSI3Ii8+CiAgICA8ZyBmaWxsLXJ1bGU9Im5vbnplcm8iIHN0cm9rZT0iI0Q5RDlEOSI+CiAgICAgIDxwYXRoIGQ9Ik01NSAxMi43Nkw0NC44NTQgMS4yNThDNDQuMzY3LjQ3NCA0My42NTYgMCA0Mi45MDcgMEgyMS4wOTNjLS43NDkgMC0xLjQ2LjQ3NC0xLjk0NyAxLjI1N0w5IDEyLjc2MVYyMmg0NnYtOS4yNHoiLz4KICAgICAgPHBhdGggZD0iTTQxLjYxMyAxNS45MzFjMC0xLjYwNS45OTQtMi45MyAyLjIyNy0yLjkzMUg1NXYxOC4xMzdDNTUgMzMuMjYgNTMuNjggMzUgNTIuMDUgMzVoLTQwLjFDMTAuMzIgMzUgOSAzMy4yNTkgOSAzMS4xMzdWMTNoMTEuMTZjMS4yMzMgMCAyLjIyNyAxLjMyMyAyLjIyNyAyLjkyOHYuMDIyYzAgMS42MDUgMS4wMDUgMi45MDEgMi4yMzcgMi45MDFoMTQuNzUyYzEuMjMyIDAgMi4yMzctMS4zMDggMi4yMzctMi45MTN2LS4wMDd6IiBmaWxsPSIjRkFGQUZBIi8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4K">
			<view style="padding-top:5px;color:#999;">暂无数据</view>
		</view>
	</view>
</template>

<script>
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
import {dateUtils} from '@/util.js';	
export default {
	components: {
		uniLoadMore
	},
	data() {
		return {
			page: 1,
			rows: [],
			listTop: 0,
			status: 'more',
			reload: false,
			contentText: {
				contentdown: '上拉加载更多',
				contentrefresh: '加载中',
				contentnomore: '没有更多'
			}
		};
	},
	onLoad() {
		var me = this;
		me.getList();
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
		me.page = 1;
		me.status = 'more';
		me.getList();
	},
	onReachBottom() {
		let me = this;
		if (me.status == 'noMore') {
			return;
		}
		me.status = 'more';
		this.getList();
	},
	methods: {
		formatDate(value) {
			return dateUtils.formatDate(value);
		},
		getList() {
			var me = this;
			if (me.status == 'noMore') {
				if (me.reload) {
					me.reload = false;
					uni.stopPullDownRefresh();
				}
				return;
			}
			me.$api.post('index/todo/index', {page: me.page}).then(res => {
				if (me.reload) {
					me.rows = [];
					me.reload = false;
					uni.stopPullDownRefresh();
				}
				
				me.rows = me.rows.concat(res.data);
				if (res.current_page >= res.last_page) {
					me.status = 'noMore';
				} else {
					me.page = res.current_page + 1;
				}
				
				if (me.rows.length > 0) {
					uni.setTabBarBadge({
						index: 0,
						text: me.rows.length + ''
					});	
				} else {
					uni.removeTabBarBadge({index: 0});
				}
				
			}).catch(error => {
			});
		},
		goDetail: function(row) {
			uni.navigateTo({
				url: '/pages/webview?title=' + row.name + '&url=' + encodeURIComponent(row.url + '?id=' + row.data_id),
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
	margin-top: 63px;
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
	display: flex;
	flex-direction: row;
	justify-content: space-between;
}

.uni-media-list-text-bottom {
	display: flex;
	flex-direction: row;
	justify-content: space-between;
	padding-top: 15rpx;
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