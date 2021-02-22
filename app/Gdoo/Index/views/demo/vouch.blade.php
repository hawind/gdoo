
    <style>
        body {
            text-align:center;
        }
        .abc {
            display: inline-block;
            text-align:left;
        }
        .table-c {
            border-right: 1px solid black;
            border-bottom: 1px solid black;
            background-color: white;
        }
        .td { padding-left: 5px; }
        .maxth {
            width: 18%;
        }
        .maxtd {
            padding-left: 5px;
        }
        .middletd {
            /*
            width: 13.5%;
            */
            padding-left: 5px;
        }
        .mintd {
        }
        .mintd-blue {
            border-right: 1px solid blue;
        }
        .mintd-red {
            border-right: 1px solid red;
        }
        .mintd-m {
            width: 3px;
        }
        .mintd, .mintd-blue, .mintd-red {
            width: 2.5%;
            text-align: center;
        }
        .td, .maxth, .maxtd, .middleth, .middletd, .mintd, .mintd-blue, .mintd-red, .mintd-m {
            border-left: 1px solid black;
            border-top: 1px solid black;
        }
        .input {
            border-left: 0px;
            border-top: 0px;
            border-right: 0px;
            border-bottom: 1px;
            width: 100%;
            height: 100%;
        }
    </style>
    <?php
        $m = mb_str_split("千百十万千百十元角分 千百十万千百十元角分");
        $jfze = 0;
        $dfze = 0;
    ?>
    <script>
        window.onresize = function () {
            resize();
        }
        $(document).ready(function () {
            resize();
        })
 
        function resize() {
            var table = document.getElementById("maintable");
            table.style.width = window.innerWidth * 0.45+"px";
            table.style.height = window.innerWidth * 0.35 / 2.8 + "px";
        }
    </script>
    <body>
    <div class="abc">
    <table>
        <tr>
            <td colspan="3" style="text-align:center;"><h2>记账凭证</h2></td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align:center;">凭证日期：2020-10-10</td>
            <td style="text-align:right;"><b>@Model[0].Pzlb 字： @Model[0].Pzxh 号</b></td>
        </tr>
        <tr>
            <td colspan="3">
                <table class="table-c" id="maintable">
                    <tr>
                        <td rowspan="2" class="maxth" style="text-align:center;"><h4><b>摘   要</b></h4></td>
                        <td colspan="2" class="td" style="text-align:center;height:30px;"><b>科    目</b></td>
                        <td colspan="10" class="td" style="text-align:center;"><b>借 方 金 额</b></td>
                        <td class="mintd-m"></td>
                        <td colspan="10" class="td" style="text-align:center;"><b>贷 方 金 额</b></td>
                    </tr>
                    <tr>
                        <td class="middleth" style="text-align:center;height:30px;"><b>科 目 代 码</b></td>
                        <td class="maxth" style="text-align:center;"><b>会 计 科 目</b></td>
                        
                        @for ($j = 1; $j <= 21; $j++)
                            @if ($j == 2 || $j == 5 || $j == 13 || $j == 16)
                                <td class="mintd-blue"><?php echo $m[$j - 1]; ?></td>
                            @elseif ($j == 8 || $j == 19)
                                <td class="mintd-red"><?php echo $m[$j - 1]; ?></td>
                            @elseif ($j == 11)
                                <td class="mintd-m"></td>
                            @else
                                <td class="mintd"><?php echo $m[$j - 1]; ?></td>
                            @endif
                        @endfor

                    </tr>
                    
                        @for ($i = 0; $i < 3; $i++)
                            <?php $jfze += 0; ?>
                            <?php $dfze += 0; ?>
                            <tr>
                                <td class="maxtd">@Model[i].Zy</td>
                                <td class="middletd">@Model[i].Kjkmdm</td>
                                <td class="maxtd">@Model[i].Kjkmmc</td>
                                <!--
                                @VouRow(Model[i].Jfje, Model[i].Dfje)
                                -->
                                @for ($j = 1; $j <= 21; $j++)
                                    @if ($j == 2 || $j == 5 || $j == 13 || $j == 16)
                                        <td class="mintd-blue"><?php echo 0; ?></td>
                                    @elseif ($j == 8 || $j == 19)
                                        <td class="mintd-red"><?php echo 0; ?></td>
                                    @elseif ($j == 11)
                                        <td class="mintd-m"></td>
                                    @else
                                        <td class="mintd"><?php echo 2; ?></td>
                                    @endif
                                @endfor

                            </tr>
                        @endfor
                    
                    <tr>
                        <td colspan="3" class="td" style="text-align:left"><b>合计：</b>@NumtoChinese(jfze)</td>
                        <!--
                        @VouRow(jfze, dfze)
                        -->
                        @for ($j = 1; $j <= 21; $j++)
                            @if ($j == 2 || $j == 5 || $j == 13 || $j == 16)
                                <td class="mintd-blue"><?php echo 11; ?></td>
                            @elseif ($j == 8 || $j == 19)
                                <td class="mintd-red"><?php echo 22; ?></td>
                            @elseif ($j == 11)
                                <td class="mintd-m"></td>
                            @else
                                <td class="mintd"><?php echo 33; ?></td>
                            @endif
                        @endfor

                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>经办人：张三</td>
            <td>记账人：李四</td>
            <td>审核人：王五</td>
        </tr>
    </table>
    </div>
    </body>
 
 <!--
    @helper VouRow(string m)
{
    for (int j = 1; j <= 21; j++)
    {
        if (j == 2 || j == 5 || j == 13 || j == 16)
        {
            <td class="mintd-blue">@m[j - 1]</td>
        }
        else if (j == 8 || j == 19)
        {
            <td class="mintd-red">@m[j - 1]</td>
        }
        else if (j == 11)
        {
            <td class="mintd-m"></td>
        }
        else
        {
            <td class="mintd">@m[j - 1]</td>
        }
    }
}
    @helper VouRow(decimal k, decimal d)
{
    string m = (k == 0 ? "".PadLeft(10) : k.ToString().Replace(".", "").PadLeft(10)) + " " + (d == 0 ? "".PadLeft(10) : d.ToString().Replace(".", "").PadLeft(10));
@VouRow(m)
}
 
    @helper NumtoChinese(decimal s)
{
                    s = Math.Round(s, 2);//四舍五入到两位小数，即分
                    string[] n = { "零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖" };
                    //数字转大写
                    string[] d = { "", "分", "角", "元", "拾", "佰", "仟", "万", "拾", "佰", "仟", "亿" };
                    //不同位置的数字要加单位
                    List<string> needReplace = new List<string> { "零拾", "零佰", "零仟", "零万", "零亿", "亿万", "零元", "零零", "零角", "零分" };
                    List<string> afterReplace = new List<string> { "零", "零", "零", "万", "亿", "亿", "元", "零", "", "" };//特殊情况用replace剔除
                    string b = "人民币";//开头
                    string e = s % 1 == 0 ? "整" : "";//金额是整数要加一个“整”结尾
                    string re = "";
                    Int64 a = (Int64)(s * 100);
                    int k = 1;
                    while (a != 0)
                    {//初步转换为大写+单位
                        re = n[a % 10] + d[k] + re;
                        a = a / 10;
                        k = k < 11 ? k + 1 : 4;
                    }
                    string need = needReplace.Where(tb => re.Contains(tb)).FirstOrDefault<string>();
                    while (need != null)
                    {
                        int i = needReplace.IndexOf(need);
                        re = re.Replace(needReplace[i], afterReplace[i]);
                        need = needReplace.Where(tb => re.Contains(tb)).FirstOrDefault<string>();
                    }//循环排除特殊情况
                    re = re == "" ? "" : b + re + e;
                     <span>@re</span>;
}
-->