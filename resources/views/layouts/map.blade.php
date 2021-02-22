@if(Request::secure())
<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=ED108c9a8758e803ace189583f27d46e&s=1"></script>
@else
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=ED108c9a8758e803ace189583f27d46e"></script>
@endif