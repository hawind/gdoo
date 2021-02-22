
  <div style="width: 100%;text-align:center;">
    <h2>康虎云报表系统报表打印测试（Ver 1.2.2）</h2>
    <h3>（PHP版演示）</h3>
    <div>
    	点按下面的“打印”按钮开始打印<br/>
      <input type="button" id="btnPrint" value="打印" onClick="doSend(_reportData);" />
    </div>
  </div>
  <div id="readme">
  	说明：<br/>
  	通过修改本页源码中的下列参数控制本页的行为：<br/>
&lt;script language="javascript" type="text/javascript"&gt;<br/>
<span style="color:green">/**下面四个参数必须放在myreport.js脚本后面，以覆盖myreport.js中的默认值**/</span><br/>
<span style="color:blue">var</span> _delay_send = <span style="color:red">1000</span>; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:green">//发送打印服务器前延时时长</span><br/>
<span style="color:blue">var</span> _delay_close = <span style="color:red">1000</span>; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:green">//打印完成后关闭窗口的延时时长, -1则表示不关闭</span><br/>
<span style="color:blue">var</span> cfprint_addr = <span style="color:red">"127.0.0.1"</span>; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:green">//打印服务器监听地址</span><br/>
<span style="color:blue">var</span> cfprint_port = <span style="color:red">54321</span>; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:green">//打印服务器监听端口</span><br/>
&lt;/script&gt;

  </div>
  <!-- 定义一个div用以显示实际发送给打印伺服程序的json，方便调试，-->
  <div id="output"></div>
</body>

<!--下面引入两个必须的 javascript 文件-->
<script language="javascript" type="text/javascript" src="{{$asset_url}}/vendor/cfprint/cfprint.min.js"></script>
<script language="javascript" type="text/javascript" src="{{$asset_url}}/vendor/cfprint/myreport.js"></script>
<!-- 下面重新设置几个参数，以覆盖myreport.js 中的默认值  -->
<script language="javascript" type="text/javascript">
/**下面四个参数必须放在myreport.js脚本后面，以覆盖myreport.js中的默认值**/
var _delay_send = -1;  //发送打印服务器前延时时长, -1则表示不自动打印
var _delay_close = -1;  //打印完成后关闭窗口的延时时长, -1则表示不关闭
var cfprint_addr = "127.0.0.1";  //打印服务器监听地址
var cfprint_port = 54321;        //打印服务器监听端口
</script>

<script type="text/javascript">
//把PHP代码里生成的数据json字符串转成javascript放在页面上，
//浏览器加载完该页面后会把该数据发送给康虎云报表伺服程序去打印
var _reportData = '<?php echo $jsonStr; ?>';

//在javascript控制台输出一下这个json字符串，对于调试有帮助
console.log("reportData = " + _reportData);
</script>