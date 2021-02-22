<template>
	<view>
		<view class="upload-box">
			<view class="uni-form-item">
				<view class="title">位置信息</view>
				<input class="uni-input" disabled="disabled" v-model="location" />
			</view>

			<view class="uni-form-item">
				<view class="title">门店名称</view>
				<input class="uni-input" placeholder="请输入门店名称" v-model="name" />
			</view>

			<view class="uni-form-item" style="border:0;">
				<img-upload :imgArr="images" imgCount="3" ref="imgUpload" />
			</view>
		</view>
		
		<view class="notice-box">
			<uni-notice-bar :show-close="true" :single="true" :text="'页面将在' + second + '秒后失效'" />
		</view>
		
		<view class="submit-box">
			<button type="primary" @click="submit">提交</button>
			<button type="info" @click="refresh">刷新</button>
		</view>
	</view>
</template>

<style>
	page {
		background-color: #fff;
	}

	.submit-box {
		display: flex;
		margin-right: 15rpx;
	}
	
	.submit-box uni-button {
		margin-left: 15rpx;
		flex-direction: row;
		width: 100%;
		justify-content: space-between;
	}
	
	.notice-box {
		padding: 0 15rpx;
	}

	.upload-box {
		padding-left: 15rpx;
	}

	.uni-form-item {
		border-bottom: 1px solid #F1F1F1;
		padding-top: 20rpx;
		padding-bottom: 20rpx;
	}

	.title {
		color: #888;
	}

	.uni-input {
		color: #222;
		padding: 4px 12px 6px 12px;
		line-height: 1.8;
	}
</style>

<script>
	var wx = require('weixin');
	import uniNoticeBar from '@/components/uni-notice-bar/uni-notice-bar.vue'
	import imgUpload from '@/components/my-components/uImgUpload.vue';
	var InterTimer = null;
	export default {
		components: {
			imgUpload,
			uniNoticeBar
		},
		data() {
			return {
				name: '',
				location: '位置获取中...',
				promotion_id: 0,
				latitude: 0,
				longitude: 0,
				images: [],
				second: 180,
			}
		},
		onLoad(options) {
			var me = this;
			me.promotion_id = options.promotion_id;
		},
		methods: {
			getLocation() {
				let me = this;
				wx.getLocation({
					type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
					success: function(res) {
						console.log('微信定位成功');
						me.latitude = res.latitude;
						me.longitude = res.longitude;
						me.$api.post('wap/wechat/mapGeocoder', {
								location: res.latitude + ',' + res.longitude
							})
							.then(ret => {
								if (ret.status == 0) {
									me.location = ret.result.address;
								}
							});
					},
					fail: function(res) {
						console.log(res);
					}
				});
			},
			setRemainTime() {
				let me = this;
				if (me.second > 0) {
					me.second = me.second - 1;
				} else {
					window.clearInterval(InterTimer);
				}
			},
			refresh() {
				let me = this;
				me.name = '';
				me.location = '';
				me.latitude = 0;
				me.longitude = 0;
				me.images = [];
				me.second = 180;
				me.$refs.imgUpload.imgArray = [];
				me.getLocation();
			},
			submit() {
				let me = this;
				
				if (me.second <= 0) {
					uni.showModal({
						content: "无法提交，表单已超时",
						confirmText: "确定",
						showCancel: false
					});
					return;
				}

				if (me.name == '') {
					uni.showModal({
						content: "门店信息不能为空",
						confirmText: "确定",
						showCancel: false
					});
					return;
				}

				if (me.location == '') {
					uni.showModal({
						content: "位置信息不能为空",
						confirmText: "确定",
						showCancel: false
					});
					return;
				}

				if (me.latitude == 0 || me.longitude == 0) {
					uni.showModal({
						content: "经纬度不能为空",
						confirmText: "确定",
						showCancel: false
					});
					return;
				}

				if (me.promotion_id == 0) {
					uni.showModal({
						content: "促销编号不能为空",
						confirmText: "确定",
						showCancel: false
					});
					return;
				}

				let files = me.$refs.imgUpload.imgArray.map((uri, index) => {
					return {
						name: "images[" + index + "]",
						uri: uri
					}
				});
				uni.showLoading({
					mask: true,
					title: '数据处理中...'
				});
				uni.uploadFile({
					url: me.$api.baseURL + "/promotion/material/store",
					files: files,
					formData: {
						name: me.name,
						location: me.location,
						lat: me.latitude,
						lng: me.longitude,
						promotion_id: me.promotion_id,
					},
					header: {
						'x-auth-token': uni.getStorageSync('token')
					},
					success: function(res) {
						uni.hideLoading();
						let ret = JSON.parse(res.data);
						if (ret.status) {
							uni.redirectTo({
								url: '/pages/app/promotionMaterial/index?promotion_id=' + me.promotion_id
							});
						} else {
							uni.showModal({
								content: ret.data,
								confirmText: "确定",
								showCancel: false
							});
						}
					}
				});
			}
		},
		mounted() {
			var me = this;
			
			InterTimer = window.setInterval(me.setRemainTime, 1000);
			
			me.$api.post('wap/wechat/jsConfig', {
				url: window.location.href
			}).then(ret => {
				wx.config(ret);
				wx.ready(function() {
					me.getLocation();
				});
				wx.error(function(res) {
					console.log(res.errMsg);
				});

			}).catch(error => {
				console.log(error);
			});
		}
	}
</script>
