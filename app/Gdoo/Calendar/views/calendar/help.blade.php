<ul class="list-group list-group-lg no-radius no-bg auto m-b-none">

	<li class="list-group-item no-border">
	    <span class="text-ellipsis">
	      	<span class="label bg-info">CalDAV URL</span>
	      	<code>{{$public_url}}/caldav</code>
             <div style="padding-top:6px;">
                安卓系统使用此地址 <a class="option" href="http://www.xijianfood.com/uploads/calendar.caldav.sync.apk">同步助手下载</a>
            </div>
	    </span>
  	</li>

  	<li class="list-group-item" style="border-bottom:0;">
	    <span class="text-ellipsis">
	      	<span class="label bg-info">CalDAV URL(<strong>IOS/OS X</strong>)</span>
	      	<code>{{$public_url}}/caldav/principals/{{Auth::user()->username}}</code>
            <div style="padding-top:6px;">
                苹果系统及苹果手机请使用此地址
            </div>
	    </span>
  	</li>
</ul>
