<template>
	<view class="page_login">
		<!-- 头部logo -->
		<view class="head">
			<view class="head_inner_title">爱客办公</view>
			<view class="head_inner_description">Gdoo Office</view>
		</view>
		<!-- 登录form -->
		<view class="login_form">
			<view class="input">
				<view class="img">
					<span class="iconfont icon-icon_signal"></span>
				</view>
				<input type="text" v-model="username" placeholder="请输入账号">
			</view>
			<view class="line" />
			<view class="input">
				<view class="img">
					<span class="iconfont icon-icon_shield"></span>
				</view>
				<input type="password" v-model="password" placeholder="请输入密码">
			</view>
		</view>
		<!-- 登录提交 -->
		<button class="submit" type="primary" @tap="login">登录</button>
		<view class="quick_login_line">
			<text class="text">爱客办公</text>
		</view>
	</view>
</template>
<script>
	export default {
		data() {
			return {
				username: '',
				password: ''
			}
		},
		methods: {
			login() {
				var me = this;
				if (me.username == '') {
					uni.showToast({
					    title: '帐号不能为空。'
					});
					return;
				}
				if (me.password == '') {
					uni.showToast({
						title: '密码不能为空。'
					});
					return;
				}
				me.$api.post('wap/auth/login', {username: me.username, password: me.password}).then(res => {
					if (res.status) {
						uni.setStorageSync('access', res.data.access);
						uni.setStorageSync('token', res.data.token);
						uni.setStorageSync('user', res.data.user);
						uni.switchTab({
							url: '/pages/tabbar/notice'
						});
					} else {
						uni.showToast({
						    title: res.data
						});
					}
				}).catch(res => {
					console.log(res);
				});
			}
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
	$logo-padding: 100rpx;
	$form-border-color: rgba(214, 214, 214, 1);
	$text-color: #B6B6B6;

	.page_login {
		padding: 10rpx;
	}
	.head {
		/*
		display: flex;
		align-items: center;
		justify-content: center;
		*/
		text-align: center;
		padding-top: $logo-padding;
		padding-bottom: $logo-padding;

		.head_inner_title {
			text-align: center;
			font-size: 50rpx;
			color: #007aff;
		}

		.head_inner_description {
			margin-top: 5rpx;
			text-align: center;
			font-size: 20rpx;
			color: #666;
		}

		.head_bg {
			border: 1px solid #8f8f8f;
			border-radius: 50rpx;
			width: 100rpx;
			height: 100rpx;
			display: flex;
			align-items: center;
			justify-content: center;

			.head_inner_bg {
				border-radius: 40rpx;
				width: 80rpx;
				height: 80rpx;
				line-height: 80rpx;
				font-size: 30rpx;
				display: flex;
				background-color: #f8f8f8;
				align-items: flex-end;
				justify-content: center;
				overflow: hidden;
			}
		}
	}

	.login_form {
		display: flex;
		margin: 40rpx;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		border: 1px solid $form-border-color;
		border-radius: 10rpx;
		background-color: #fff;

		.line {
			width: 100%;
			height: 1px;
			background-color: $form-border-color;
		}

		.input {
			width: 100%;
			max-height: 45px;
			display: flex;
			padding: 3rpx;
			flex-direction: row;
			align-items: center;
			justify-content: center;

			.img {
				min-width: 40px;
				min-height: 40px;
				margin: 5px;
				display: flex;
				align-items: center;
				justify-content: center;

				.iconfont {
					font-size: 45rpx;
					color: #999;
				}
			}

			input {
				outline: none;
				height: 30px;
				width: 100%;

				&:focus {
					outline: none;
				}
			}
		}
	}

	.submit {
		margin-top: 30px;
		margin-left: 20px;
		margin-right: 20px;
		color: white;
	}

	.quick_login_line {
		/*
		margin-top: 40px;
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: center;
		*/
		position: absolute;
		bottom: 30rpx;
		left: 50%;
		transform: translate(-50%, 0);

		.text {
			text-align: center;
			font-size: 13px;
			color: #aaa;
			margin: 2px;
		}
	}
</style>
