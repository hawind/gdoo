<script>
export default {
	onLaunch: function() {
		console.log('App Launch');
		/*
		setTimeout(() => {
			uni.setTabBarBadge({
				index: 1,
				text: '9'
			});
			uni.showTabBarRedDot({
				index: 3
			});
		}, 1000);
		*/
	},
	onShow: function() {
		console.log('App Show');
	},
	onHide: function() {
		console.log('App Hide');
	}
};
</script>

<style>
@import './static/uni.css';
/*每个页面公共css */ 
@import './static/iconfont/iconfont.css';
</style>
