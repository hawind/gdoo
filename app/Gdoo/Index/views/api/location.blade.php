<script type="text/javascript">
$(document).ready(function() {
    // 百度地图API功能
	var map = new BMap.Map("mapinfo");
	var point = new BMap.Point({{$gets['lng']}},{{$gets['lat']}});
	map.centerAndZoom(point, 15);
    // 创建标注
	var marker = new BMap.Marker(point);
    // 将标注添加到地图中
	map.addOverlay(marker);
    // 鼠标滑轮缩放
    map.enableScrollWheelZoom();
});
</script>
<div id="mapinfo" style="height:430px;">地图加载中...</div>