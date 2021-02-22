<template>
	<view class="w-100">
		<view class="w-100 flex_wrap">
			<view class="imgs-view" v-for="(v,i) in imgArray" :key="i">
				<image @click="preview(v,i)" :src="v"></image>
				<view class="del-btn" @click="delImg(i)">
					<image src="@/components/my-components/imgs/delete.png"></image>
				</view>
			</view>
			<view v-if="imgArray.length < imgCount" class="upload-img-view flex_xy_center" @click="upPhoto">
				<image src="@/components/my-components/imgs/jia.png"></image>
			</view>
		</view>
		<view class="tip">* 最多上传{{imgCount}}张图片(<label> {{imgArray.length}} </label>/{{imgCount}})</view>
	</view>
</template>

<script>
	export default {
		name: 'imgUpload',
		props: {
			imgArr: { // 图片数组
				type: [Array],
			},
			uploadImgCount: { // 一次上传图片数
				type: String,
				default: '3'
			},
			imgCount: { // 可上传图片总数
				type: String,
				default: '3'
			},
			imgSize: { //图片大小 单位M
				type: Number,
				default: 10
			},
			imgType: {
				type: [Array],
				default: function() {
					return ['jpeg', 'png', 'jpg']
				}
			}
		},
		created() {
			this.imgArray = this.imgArr;
		},
		data() {
			return {
				imgArray: []
			}
		},
		watch: {},
		methods: {
			upPhoto() {
				let me = this;
				if (Number(me.imgCount - me.imgArray.length) < Number(me.uploadImgCount)) {
					me.uploadImgCount = Number(me.imgCount - me.imgArray.length);
				}
				uni.chooseImage({
					count: Number(me.uploadImgCount),
					sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
					sourceType: ['camera'], // 从相册选择
					success: function(res) {
						if (res) {
							if (res.tempFiles instanceof Array && res.tempFiles) {
								for (let item of res.tempFiles) {
									if (item.size > (me.imgSize * 1024 * 1024)) {
										uni.showToast({
											title: `图片不能大于${me.imgSize}M`,
											icon: 'none'
										})
										return false;
									}
									let r = me.imgType.some(v => {
										let type = item.type.split('/');
										if (type.length)
											return (v === type[1]);
									});
									if (!r) {
										uni.showToast({
											title: `只允许上传${me.imgType}的类型`,
											icon: 'none'
										})
										return false;
									}
								}
							}
							me.imgArray = [...me.imgArray, ...res.tempFilePaths];
						}
					}
				});
			},
			preview(url, index) {
				// 预览图片
				uni.previewImage({
					urls: [url]
				});
			},
			delImg(i) {
				const me = this;
				uni.showModal({
					title: '提示',
					content: '是否删除这张照片？',
					success: function(res) {
						if (res.confirm) {
							me.imgArray.splice(i, 1);
						} else if (res.cancel) {}
					}
				});
			},
			async upload(callback) {
				const me = this;
				if (me.imgArray) {
					let successNum = 0;
					let urlArr = [];
					for (let item of me.imgArray) {
						await me.uploadImg(item, res => {
							if (res.code == 200) { //接口回调值 *注意修改为自己接口对应的！！！
								successNum++;
								if (res.data.code == 200) { //接口回调值 *注意修改为自己接口对应的！！！
									urlArr.push(res.data.data);
								}
							} else {
								urlArr.push(res);
							}
							if (urlArr.length == me.imgArray.length) {
								callback(me.result(urlArr, successNum));
							}
						});
					}
				}
			},
			result(urlArr, successNum) {
				let result = {
					urlArray: urlArr,
					success: successNum
				}
				return result;
			},
			uploadImg(item, callback) {
				const me = this;
				uni.uploadFile({
					url: $config.SERVER_URL + 'api/sys/user/modify/uploadPic', // 自行修改各自的对应的接口 
					filePath: item,
					name: 'file',
					success: (uploadFileRes) => {
						if (uploadFileRes) {
							let res = JSON.parse(uploadFileRes.data);
							callback(res);
						}
					}
				});
			}
		}
	}
</script>

<style scoped>
	.w-100 {
		width: 100%;
	}
	.flex {
		/* 转为弹性盒模型*/
		display: flex;
	}
	.flex_bet {
		/* 两端左右*/
		display: flex;
		justify-content: space-between;
	}
	.flex_wrap {
		/* 转为弹性盒模型并自动换行*/
		display: flex;
		flex-wrap: wrap;
	}
	.upload-img-view {
		height: 200upx;
		width: 31.5%;
		border-radius: 0;
		border: 1upx solid #F1F1F1;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	.upload-img-view>image {
		width: 60upx;
		height: 60upx;
	}
	.imgs-view {
		height: 200upx;
		width: 32%;
		border-radius: 0;
		margin-right: 12upx;
		margin-bottom: 12upx;
		border: 1upx solid #F1F1F1;
		box-sizing: border-box;
		position: relative;
	}
	.imgs-view:last-child {
		margin-right: 0;
	}
	.imgs-view>image {
		width: 100%;
		height: 100%;
		border-radius: 0;
	}
	.tip {
		font-size: 24upx;
		color: #999;
		margin-top: 12upx;
	}
	.tip > label {
		color: #009100;
	}
	.del-btn {
		position: absolute;
		top: 2rpx;
		right: 2rpx;
		width: 32upx;
		height: 32upx;
		z-index: 999;
	}
	.del-btn>image {
		width: 100%;
		height: 100%;
		display: flex;
	}
</style>
