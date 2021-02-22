<template>
	<view class="content">
		<uni-list>
			<uni-list-item title="头像" />
			<uni-list-item title="姓名" :rightText="name" />
			<uni-list-item title="帐号" :rightText="username" />
			<!--
			<uni-list-item title="我的订单" />
			<uni-list-item title="我的发货" />
			-->
		</uni-list>
		<view class="btns">
			<button @click="logout" type="warn" class="text">{{logout_text}}</button>
		</view>
	</view>
</template>

<script>
	import uniSection from '@/components/uni-section/uni-section.vue'
	import uniList from '@/components/uni-list/uni-list.vue'
	import uniListItem from '@/components/uni-list-item/uni-list-item.vue'
	export default {
		components: {
			uniSection,
			uniList,
			uniListItem
		},
		data() {
			return {
				name: '',
				username: '',
				logout_text: '帐号注销'
			}
		},
		onLoad() {
			let me = this;
			let user = uni.getStorageSync('user');
			me.name = user.name;
			me.username = user.username;
		},
		methods: {
			logout() {
				this.$api.logout();
			},
			openurl() {
				uni.navigateTo({
					url: '/pages/webview'
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
.content {
}
.uni-section {
	margin-top: 0;
}
.btns {
	padding: 30rpx;
}
</style>
