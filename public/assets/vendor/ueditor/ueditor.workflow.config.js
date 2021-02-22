(function () {

    var URL = window.UEDITOR_HOME_URL;
    var initialStyle = "html.view {background:url(/assets/images/wf_canvas_bg.png)} body.view {background:#fff;padding:10px; width:820px;margin:0 auto; font:12px/18px '微软雅黑',tahoma,Arial,Verdana,Helvetica,sans-serif;color:#333;border:1px solid #ddd;}" + 
    'body.view > table {width:800px;}' +
    'body.view > table td,body.view > table th {border-bottom: 1px solid #ddd;border-left:1px solid #ddd;padding:6px;}' +
    'body.view > table th {color:#333;}' +
    'body.view > table thead th {background:#eee;color:#666;}';

    window.UEDITOR_CONFIG = {

        UEDITOR_HOME_URL:URL
        //工具栏上的所有的功能按钮和下拉框，可以在new编辑器的实例时选择自己需要的从新定义
        ,toolbars:[["source","undo","redo","unlink","link","insertunorderedlist","insertorderedlist","spechars","template","bold","italic","underline","strikethrough","forecolor","backcolor","justifyleft","justifycenter","justifyright","justifyjustify","pasteplain","formatmatch","removeformat","rowspacingbottom","rowspacingtop","fontsize","fontfamily","inserttable","deletetable","mergeright","mergedown","splittorows","splittocols","splittocells","mergecells","insertcol","insertrow","deletecol","deleterow","insertparagraphbeforetable","imagecenter","imageright","imageleft","imagenone"]]
        //当鼠标放在工具栏上时显示的tooltip提示,留空支持自动多语言配置，否则以配置值为准
        ,labelMap:{
            'anchor':'','undo':''
        }
        // 禁止将div转换p
        ,allowDivTransToP:false
        // 允许表格嵌套
        ,disabledTableInTable:false

        //webAppKey
        //百度应用的APIkey，每个站长必须首先去百度官网注册一个key后方能正常使用app功能
        ,webAppKey:""


        //语言配置项,默认是zh-cn。有需要的话也可以使用如下这样的方式来自动多语言切换，当然，前提条件是lang文件夹下存在对应的语言文件：
        //lang值也可以通过自动获取 (navigator.language||navigator.browserLanguage ||navigator.userLanguage).toLowerCase()
        //,lang:'zh-cn'

        //,langPath:URL +"lang/"

        //主题配置项,默认是default。有需要的话也可以使用如下这样的方式来自动多主题切换，当然，前提条件是themes文件夹下存在对应的主题文件：
        //现有如下皮肤:default,modern,gorgeous
        //,theme:'default'
        //,themePath:URL +"themes/"

        //若实例化编辑器的页面手动修改的domain，此处需要设置为true
        //,customDomain:false

        //针对getAllHtml方法，会在对应的head标签中增加该编码设置。
        //,charset:"utf-8"

        //常用配置项目
        //,isShow : true    //默认显示编辑器

        ,initialContent:''    //初始化编辑器的内容,也可以通过textarea/script给值，看官网例子

        //,initialFrameWidth:1000  //初始化编辑器宽度,默认1000
        //,initialFrameHeight:320  //初始化编辑器高度,默认320

        //,autoClearinitialContent:true //是否自动清除编辑器初始内容，注意：如果focus属性设置为true,这个也为真，那么编辑器一上来就会触发导致初始化的内容看不到了
        ,iframeCssUrl:URL + '../../../assets/dist/app.min.css'
        //,iframeCssUrl:URL + 'themes/app.css' //给编辑器内部引入一个css文件
        //,textarea:'editorValue' // 提交表单时，服务器获取编辑器提交内容的所用的参数，多实例时可以给容器name属性，会将name给定的值最为每个实例的键值，不用每次实例化的时候都设置这个值
        //,focus:true //初始化时，是否让编辑器获得焦点true或false

        //,autoClearEmptyNode : true //getContent时，是否删除空的inlineElement节点（包括嵌套的情况）

        //,fullscreen : false //是否开启初始化时即全屏，默认关闭

        //,readonly : false /编辑器初始化结束后,编辑区域是否是只读的，默认是false

        //,zIndex : 900     //编辑器层级的基数,默认是900

        //,imagePopup:true      //图片操作的浮层开关，默认打开

        ,initialStyle:initialStyle   //编辑器内部样式,可以用来改变字体等

        //,emotionLocalization:false //是否开启表情本地化，默认关闭。若要开启请确保emotion文件夹下包含官网提供的images表情文件夹

        //,pasteplain:false  //是否纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴

        //,allHtmlEnabled:false //提交到后台的数据是否包含整个html字符串
        //iframeUrlMap
        //dialog内容的路径 ～会被替换成URL,垓属性一旦打开，将覆盖所有的dialog的默认路径
        //,iframeUrlMap:{
        // 'anchor':'~/dialogs/anchor/anchor.html',
        // }
        //insertorderedlist
        //有序列表的下拉配置,值留空时支持多语言自动识别，若配置值，则以此值为准
//        ,'insertorderedlist':{
//             'decimal' : '' ,         //'1,2,3...'
//             'lower-alpha' : '' ,    // 'a,b,c...'
//             'lower-roman' : '' ,    //'i,ii,iii...'
//             'upper-alpha' : '' , lang   //'A,B,C'
//             'upper-roman' : ''      //'I,II,III...'
//        }
        //insertunorderedlist
        //无序列表的下拉配置，值留空时支持多语言自动识别，若配置值，则以此值为准
        //,insertunorderedlist : {
        //    'circle' : '',  // '○ 小圆圈'
        //    'disc' : '',    // '● 小圆点'
        //    'square' : ''   //'■ 小方块'
        //}
        //fontfamily
        //字体设置 label留空支持多语言自动切换，若配置，则以配置值为准
        //,'fontfamily':[
        // { label:'',name:'songti',val:'宋体,SimSun'},
        //   { label:'',name:'kaiti',val:'楷体,楷体_GB2312, SimKai'},
        //   { label:'',name:'yahei',val:'微软雅黑,Microsoft YaHei'},
        //   { label:'',name:'heiti',val:'黑体, SimHei'},
        //   { label:'',name:'lishu',val:'隶书, SimLi'},
		//	  { label:'',name:'STZhongsong',val:'华文中宋, STZhongsong'},
        //   { label:'',name:'andaleMono',val:'andale mono'},
        //   { label:'',name:'arial',val:'arial, helvetica,sans-serif'},
        //   { label:'',name:'arialBlack',val:'arial black,avant garde'},
        //   { label:'',name:'comicSansMs',val:'comic sans ms'},
        //   { label:'',name:'impact',val:'impact,chicago'},
        //   { label:'',name:'timesNewRoman',val:'times new roman'}
        //]
        //fontsize
        //字号
        //,'fontsize':[10, 11, 12, 14, 16, 18, 20, 24, 36]
        //paragraph
        //段落格式 值留空时支持多语言自动识别，若配置，则以配置值为准
        //,'paragraph':{'p':'', 'h1':'', 'h2':'', 'h3':'', 'h4':'', 'h5':'', 'h6':''}
        //rowspacingtop
        //段间距 值和显示的名字相同
        //,'rowspacingtop':['5', '10', '15', '20', '25']
        //rowspacingBottom
        //段间距 值和显示的名字相同
        //,'rowspacingbottom':['5', '10', '15', '20', '25']
        //lineheight
        //行内间距 值和显示的名字相同
        //,'lineheight':['1', '1.5','1.75','2', '3', '4', '5']

        //customstyle
        //自定义样式，不支持国际化，此处配置值即可最后显示值
        //block的元素是依据设置段落的逻辑设置的，inline的元素依据BIU的逻辑设置
        //尽量使用一些常用的标签
        //参数说明
        //tag 使用的标签名字
        //label 显示的名字也是用来标识不同类型的标识符，注意这个值每个要不同，
        //style 添加的样式
        //每一个对象就是一个自定义的样式
        //,'customstyle':[
        //      {tag:'h1', name:'tc', label:'', style:'border-bottom:#ccc 2px solid;padding:0 4px 0 0;text-align:center;margin:0 0 20px 0;'},
        //      {tag:'h1', name:'tl',label:'', style:'border-bottom:#ccc 2px solid;padding:0 4px 0 0;margin:0 0 10px 0;'},
        //      {tag:'span',name:'im', label:'', style:'font-style:italic;font-weight:bold'},
        //      {tag:'span',name:'hi', label:'', style:'font-style:italic;font-weight:bold;color:rgb(51, 153, 204)'}
        //  ]

        //右键菜单的内容，可以参考plugins/contextmenu.js里边的默认菜单的例子，label留空支持国际化，否则以此配置为准
//        ,contextMenu:[
//            {
//                label:'',       //显示的名称
//                cmdName:'selectall',//执行的command命令，当点击这个右键菜单时
//                //exec可选，有了exec就会在点击时执行这个function，优先级高于cmdName
//                exec:function () {
//                    //this是当前编辑器的实例
//                    //this.ui._dialogs['inserttableDialog'].open();
//                }
//            }
//           ]
        //wordCount
        //,wordCount:1          //是否开启字数统计
        //,maximumWords:10000       //允许的最大字符数
    //字数统计提示，{#count}代表当前字数，{#leave}代表还可以输入多少字符数,留空支持多语言自动切换，否则按此配置显示
        //,wordCountMsg:''   //当前已输入 {#count} 个字符，您还可以输入{#leave} 个字符
        //超出字数限制提示  留空支持多语言自动切换，否则按此配置显示
        //,wordOverFlowMsg:''    //<span style="color:red;">你输入的字符个数已经超出最大允许值，服务器可能会拒绝保存！</span>

        //highlightcode
        // 代码高亮时需要加载的第三方插件的路径
        // ,highlightJsUrl:URL + "third-party/SyntaxHighlighter/shCore.js"
        // ,highlightCssUrl:URL + "third-party/SyntaxHighlighter/shCoreDefault.css"
        //elementPathEnabled
        //是否启用元素路径，默认是显示
        //,elementPathEnabled : true
        //removeFormat
        //清除格式时可以删除的标签和属性
        //removeForamtTags标签
        //,removeFormatTags:'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var'
        //removeFormatAttributes属性
        //,removeFormatAttributes:'class,style,lang,width,height,align,hspace,valign'
        //undo
        //可以最多回退的次数,默认20
        //,maxUndoCount:20
        //当输入的字符数超过该值时，保存一次现场
        //,maxInputCount:1
        //autoHeightEnabled
        // 是否自动长高,默认true
        //,autoHeightEnabled:true
        //,minFrameWidth:800    //编辑器拖动时最小宽度,默认800
        //,minFrameHeight:220  //编辑器拖动时最小高度,默认220
        //autoFloatEnabled
        //是否保持toolbar的位置不动,默认true
        ,autoFloatEnabled:false
        //浮动时工具栏距离浏览器顶部的高度，用于某些具有固定头部的页面
        //,topOffset:30
        //indentValue
        //首行缩进距离,默认是2em
        //,indentValue:'2em'
        //pageBreakTag
        //分页标识符,默认是_baidu_page_break_tag_
        //,pageBreakTag:'_baidu_page_break_tag_'

        //autotypeset
        //  //自动排版参数
        //  ,autotypeset:{
        //      mergeEmptyline : true,         //合并空行
        //      removeClass : true,           //去掉冗余的class
        //      removeEmptyline : false,      //去掉空行
        //      textAlign : "left" ,           //段落的排版方式，可以是 left,right,center,justify 去掉这个属性表示不执行排版
        //      imageBlockLine : 'center',      //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
        //      pasteFilter : false,            //根据规则过滤没事粘贴进来的内容
        //      clearFontSize : false,          //去掉所有的内嵌字号，使用编辑器默认的字号
        //      clearFontFamily : false,        //去掉所有的内嵌字体，使用编辑器默认的字体
        //      removeEmptyNode : false ,       // 去掉空节点
        //      //可以去掉的标签
        //      removeTagNames : {标签名字:1},
        //      indent : false,                 // 行首缩进
        //      indentValue : '2em'             //行首缩进的大小
        //  }

    };
})();
