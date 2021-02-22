<template>
	<view class="page_index">
		<iframe class="webview" ref="iframe" :src="url" id="iframe"></iframe>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				url: ''
			}
		},
		mounted () {
			var me = this;
			uni.showLoading({
				title:'页面加载中...',
			});
			const iframe = document.querySelector('#iframe');
			iframe.onload = function () {
				uni.hideLoading();
			}
		},
		onLoad: function(options) {
			var me = this;
			document.title = options.title;
			var url = decodeURIComponent(options.url);
			var token = uni.getStorageSync('token');
			if (token) {
				me.url = me.$api.baseURL + '/' + url + '&x-auth-token=' + token;
			} else {
				me.$api.authorize();
			}
		},
		methods: {
		}
	}
</script>
<style>
	page {
		height: auto;
		min-height: 100%;
		background-color: #f0f3f4;
		font-size: 14px;
	}
	.uni-page-head {
		border-bottom: 3px solid #333;
	}
</style>
<style lang="scss" scoped>
	.webview {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		border: 0;
		width: 100vw;
		height: 100vh;
		background-color: #f0f3f4;
	}
	.page_index {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100vh;
	}
	.head {
		position: relative;
		transform: translateY(-50%);
		.head_inner_title {
			text-align: center;
			font-size: 60rpx;
			color: #007aff;
			display: block;
		}
		.head_inner_description {
			margin-top: 5rpx;
			text-align: center;
			font-size: 30rpx;
			color: #666;
			display: block;
		}
	}
</style>
