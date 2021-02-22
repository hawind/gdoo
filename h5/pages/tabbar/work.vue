<template>
	<view class="content">
		<!-- 一般用法 -->
		<view class="example-body">
		<uni-grid :column="3">
		    <uni-grid-item v-for="(item, index) in list" :index="index" :key="index">
				<view class="grid-item-box" @click="openURL(item)">
					<span :class="'iconfont ' + item.icon" :style="'color:' + item.icon_color"></span>
					<text class="text">{{ item.text }}</text>
					<view v-if="item.badge" class="grid-dot">
						<uni-badge :text="item.badge" :type="item.type" />
					</view>
				</view>
		    </uni-grid-item>
		</uni-grid>
		</view>
	</view>
</template>

<script>
	
import uniGrid from "@/components/uni-grid/uni-grid.vue"
import uniGridItem from "@/components/uni-grid-item/uni-grid-item.vue"
import uniBadge from '@/components/uni-badge/uni-badge.vue'
	export default {
		components: {uniGrid,uniGridItem,uniBadge},
		data() {
			return {
				list: [{
						url: '/pages/app/saleOrder/index',
						icon: 'icon-icon_dispose',
						text: '销售订单',
						icon_color: '#007aff',
						access: 1,
						// badge: '0',
						type: "primary"
					},{
						url: '/pages/app/article/index',
						icon: 'icon-icon_notice',
						icon_color: '#22ac38',
						text: '新闻公告',
						access: 1,
						// badge: '0',
						type: "success"
					},
					{
						url: '/pages/app/delivery/index',
						icon: 'icon-icon_send',
						icon_color: '#ff9900',
						text: '发货列表',
						access: 1,
						// badge: '0',
						type: "warning"
					},{
						url: '/pages/app/promotion/index',
						icon: 'icon-icon_nomemo',
						icon_color: '#22ac38',
						text: '促销列表',
						access: 1,
						// badge: '0',
						type: "warning"
					},{
						url: '/pages/app/approach/index',
						icon: 'icon-icon_synergy',
						icon_color: '#007aff',
						text: '进店列表',
						access: 1,
						// badge: '0',
						type: "warning"
					},{
						url: '/pages/app/workflow/index',
						icon: 'icon-icon_subordinate',
						icon_color: '#9370DB',
						access: 1,
						text: '工作流程',
						//badge: '0',
						type: "warning"
					},{
						url: '/pages/app/workflow/index',
						icon: 'icon-icon_task_done',
						icon_color: '#007aff',
						access: 1,
						text: '工作任务',
						// badge: '0',
						type: "warning"
					},{
						url: '/pages/app/workflow/index',
						icon: 'icon-icon_calendar',
						icon_color: '#ff9900',
						access: 1,
						text: '个人日程',
						// badge: '0',
						type: "warning"
					},{
						url: '/pages/app/workflow/index',
						icon: 'icon-icon_group',
						icon_color: '#22ac38',
						access: 1,
						text: '客户列表',
						// badge: '0',
						type: "warning"
					}
				]
			}
		},
		onLoad() {
		},
		methods: {
			openURL(item) {
				uni.navigateTo({
				    url: item.url
				});
			}
		}
	}
</script>

<style>
	view {
		font-size: 14px;
		line-height: inherit;
	}
	.content {
		text-align: center;
		padding: 15rpx;
	}
	.image {
		width: 50rpx;
		height: 50rpx;
	}
	
	.text {
		font-size: 26rpx;
		margin-top: 10rpx;
		color: #666;
	}
	
	.grid-dynamic-box {
		margin-bottom: 15px;
	}
	
	.grid-item-box {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 15px 0;
	}
	
	.grid-item-box .iconfont {
		font-size: 80rpx;
	}
	
	.grid-dot {
		position: absolute;
		top: 5px;
		right: 15px;
	}
</style>
