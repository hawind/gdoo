
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>{{$setting['title']}}</title>
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta name="format-detection" content="telephone=no"/>
<meta name="format-detection" content="email=no"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=0"/>
<link rel="stylesheet" type="text/css" href="{{$asset_url}}/chat/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="{{$asset_url}}/chat/css/webimcss.css"/>

<link rel="stylesheet" type="text/css" href="{{$asset_url}}/chat/jquery/perfectscrollbar/perfect-scrollbar.css"/>
<link rel="stylesheet" type="text/css" href="{{$asset_url}}/chat/jquery/menu/jquery-rockmenu.css"/>
<link rel="stylesheet" type="text/css" href="{{$asset_url}}/chat/css/chat.css"/>
<link rel="shortcut icon" id="ico" href="{{$asset_url}}/chat/images/web/logo.png" />
<script type="text/javascript" src="{{$asset_url}}/vendor/jquery.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/js/js.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/js/nwjs.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/menu/jquery-rockmenu.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/js/notify.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/js/strformat.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/js/realtime.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/js/websocket.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/perfectscrollbar/perfect-scrollbar.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/perfectscrollbar/jquery.mousewheel.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/jquery-imgview.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/jquery-rockupload.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/jquery-rockmodels.js"></script>
<script type="text/javascript" src="{{$asset_url}}/chat/jquery/jquery-changeuser.js"></script>
<script>
js.servernow = '{{date("Y-m-d H:i:s")}}';
companymode = false;
function globalbody() {
	adminid = '{{$user["id"]}}';
	adminface = '{{avatar($user["avatar"])}}';
	adminname = '{{$user["name"]}}';
	adminuser = '{{$user["username"]}}';
	deptallname	= '{{$user->department->name}}';
	adminranking = '{{$user->role->name}}';
}
function initbody() {
	reim.init();
}
function winfocus() {
	window.focus();
}
</script>
</head>

<body style="overflow:hidden;" oncontextmenu="return true">

<div style="position:absolute;bottom:15px;left:0;width:60px;">
	<div align="center" id="reimcog" class="cursor" style="color:#fff;font-size:16px">
		<i class="fa fa-cog"></i>
	</div>
</div>

<div id="mindivshow" style="height:538px;overflow:hidden;" class="mindivshow">

	<table style="width:100%;" height="100%">
		<tr valign="top">
		<td height="100%" width="60" style="background:#1890ff;">
			<div align="center" style="width:60px;overflow:hidden;">
				<div style="margin-top:20px"><img title="{{$user['name']}}" onclick="reim.openmyinfo()" src="{{avatar($user['avatar'])}}" id="myface" style="border-radius:50%;" align="absmiddle" height="40" width="40">
				</div>
				<div style="margin-top:20px;">
					<div class="cursor lefticons active" id="changetabs0" onclick="reim.changetabs(0)" title="消息">
						<i class="fa fa-comments-o"></i>
						<span id="chat_stotal" class="badge"></span>
					</div>
				</div>
				<div style="margin-top:10px;">
					<div class="cursor lefticons" id="changetabs1" onclick="reim.changetabs(1)" title="组织结构">
						<i class="fa fa-sitemap"></i>
					</div>
				</div>
				<div style="margin-top:10px;">
					<div class="cursor lefticons" id="changetabs2" onclick="reim.changetabs(2)" title="应用">
						<i class="fa fa-th-large"></i>
						<span id="agenh_stotal" class="badge"></span>
					</div>
				</div>
			</div>
		</td>
		<td width="220px" id="maincenter" style="background:#fff;border-right:1px solid #ddd">

			<div class="chat_search">
				<input id="reim_keysou" placeholder="搜索通讯录/会话/应用" class="msousou" />
				<a class="plus" title="创建会话" onclick="reim.creategroup();">+</a>
			</div>

			<div id="centlist" style="height:300px;overflow:hidden;position:relative;">

				<div id="centshow0">
					<div id="historylist"></div>
					<div id="historylist_tems" style="padding-top:150px;text-align:center;color:#ddd">
					<span style="font-size:40px"><i class="fa fa-comment"></i></span><br>暂无消息
					</div>
				</div>
				<div id="centshow1" style="display:none">
					<div style="padding:5px;color:#aaaaaa;border-bottom:1px solid #f1f1f1">组织结构</div>
					<div id="showdept"></div>
					<div id="showgroup"></div>
					<div align="center" style="padding:10px;"><a onclick="reim.initload(true)" style="font-size:12px;color:#bbbbbb" href="javascript:;"><i class="icon-refresh"></i> 刷新</a></div>
				</div>
			</div>
		</td>
		<td>
			<div id="viewzhulist" style="height:300px;overflow:hidden;background:#f0f3f4;">
				<div align="center" tabs="home" id="tabs_home" style="margin-top:100px;font-size:150px;color:#edf4fb">
					<i class="fa fa-comment-o"></i>
				</div>	
			</div>
		</td>
		</tr>
	</table>
</div>
</body>
</html>