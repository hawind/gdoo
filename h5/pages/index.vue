<template>
	<view class="page_index">
		<view class="head">
			<view class="head_inner_title">爱客办公</view>
			<view class="head_inner_description">Gdoo Office</view>
		</view>
	</view>
</template>
<script>
	export default {
		data() {
			return {}
		},
		onLoad: function() {
			var me = this;
			me.$api.authorize();
		},
		methods: {
		}
	}
</script>
<style>
	page {
		height: auto;
		min-height: 100%;
		background-color: #f5f6f8;
		font-size: 14px;
	}
</style>
<style lang="scss" scoped>
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
			font-size: 50rpx;
			color: #007aff;
			display: block;
		}

		.head_inner_description {
			margin-top: 5rpx;
			text-align: center;
			font-size: 20rpx;
			color: #666;
			display: block;
		}
	}
</style>
