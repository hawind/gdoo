<style>
.red_text td,
.red_text td span { color:red; }
</style>

<div class="panel">

    <div class="wrapper-sm b-b b-light">
        <div title="今天是本年第{{$days}}天" class="text-md">{{$now_year}}年经销商销售排行</div>
    </div>

    <div class="wrapper-sm b-b b-light">
        
        <form class="form-inline" id="myquery" name="myquery" action="{{url()}}" method="get">

            <div class="pull-right">
                <a class="btn btn-default btn-sm" onclick="LocalTableExport('report_ranking', '销售排名');"><i class="fa fa-mail-forward"></i> 导出</a>
            </div>

            @if(Auth::user()->role->code != 'c001')
            @include('report/select')
            &nbsp;
            @endif
            客户
            <input class="form-control input-sm" value="{{$select['query']['customer_name']}}" name="customer_name">

            <select class="form-control input-sm" id='tag' name='tag' data-toggle="redirect" data-url="{{$query}}">
                <option value="city_id" @if($select['query']['tag']=='city_id') selected @endif>城市模式</option>
                <option value="customer_id" @if($select['query']['tag']=='customer_id') selected @endif>客户模式</option>
            </select>
            <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i> 搜索</button>
        </form>
        
    </div>

    <table class="table table-bordered" id="report_ranking">
    	<tr>
    	<th width="80">排行</th>
    	@if($select['query']['tag'] == 'customer_id')
            <th style="white-space:nowrap;">销售组</th>
    		<th style="white-space:nowrap;">负责人</th>
    	@endif
        @if($select['query']['tag']=='city_id')
    	    <th align="center" style="white-space:nowrap;">省份</th>
            <th align="center" style="white-space:nowrap;">城市</th>
        @else
            <th align="center" style="white-space:nowrap;">客户编码</th>
            <th align="center" style="white-space:nowrap;">客户名称</th>
            <th align="center" style="white-space:nowrap;">销售等级</th>
        @endif
        </th>
    	<th style="white-space:nowrap;">比去年同期增长率</th>
        <th style="white-space:nowrap;">比去年同期增长额</th>
    	<th style="white-space:nowrap;">总销售额</th>
        <th style="white-space:nowrap;">销售额占区域比</th>
        <th style="white-space:nowrap;">增长额总销售贡献比</th>
        <th style="white-space:nowrap;">增长贡献率</th>
         @foreach($categorys['name'] as $category)
    		<th align="center" style="white-space:nowrap;">{{$category['name']}}</th>
    	 @endforeach

    	<?php
            $this_year_data = $single[$now_year];
            if ($this_year_data) {
                arsort($this_year_data);
            }
            $last_year_data = $single[$last_year];
            $i = 0;
            $total = [];

            $growth = [
                // 今年比去年同期增加额求和
                'a' => [],
                // 今年比去年同期增加额正数求和
                'b' => [],
                // 今年比去年同期增加额负数求和
                'c' => []
            ];

            foreach((array)$this_year_data as $k => $v) {
                $res = $v - $last_year_data[$k];
                $growth['a'][$k] += $res;
                // 正数求和
                if($res > 0) {
                    $growth['b'][$k] += $res;
                }
                // 负数求和
                if($res < 0) {
                    $growth['c'][$k] += $res;
                }
            }

            $this_year_sum = array_sum((array)$this_year_data);
            $last_year_sum = array_sum((array)$last_year_data);
            $growth_a = array_sum($growth['a']);
            $growth_b = array_sum($growth['b']);
            $growth_c = array_sum($growth['c']);

        ?>

            @foreach($this_year_data as $k => $v)

        	<?php
                $i++;
                $total['all'] += $this_year_data[$k];
            ?>

        	<tr class="@if(($v - $last_year_data[$k]) < 0) red_text @endif">
        	    <td align="center">{{$i}}</td>

                @if($select['query']['tag'] == 'customer_id')
                    <td align="center">{{$regions[$single['info'][$k]['region_id']]['name']}}</td>
        	        <td align="center">{{get_user($regions[$single['info'][$k]['region_id']]['owner_user_id'], 'name')}}</td>
                @endif

                @if($select['query']['tag'] == 'city_id')
                    <td align="center">
                        {{$single['info'][$k]['province_name']}}
                    </td>
                    <td align="center">
                        {{$single['info'][$k]['city_name']}}
                    </td>
                @else
                    <td align="center">
                        {{$single['info'][$k]['customer_code']}}
                    </td>
                    <td align="left">
                        <!-- 客户类型 -->
                        <?php 
                            $post = $single['info'][$k]['grade_id'];
                            // 今年合计
                            $now_year_money = $this_year_data[$k];
                            $grade = $now_year_money / $days;
                            $post_type = '';
                            if ($post == 1) {
                                if ($grade > 32877) {
                                    $post_type = '军';
                                } elseif ($grade > 16438) {
                                    $post_type = '师';
                                } elseif ($grade > 6575) {
                                    $post_type = '旅';
                                } elseif ($grade > 3288) {
                                    $post_type = '团';
                                } elseif ($grade > 1643) {
                                    $post_type = '营';
                                } elseif ($grade > 657) {
                                    $post_type = '连';
                                } else {
                                    $post_type = '问题';
                                }
                            } else if($post == 2) {
                                if ($grade > 3288) {
                                    $post_type = '大队';
                                } elseif ($grade > 1643) {
                                    $post_type = '中队';
                                } elseif ($grade > 657) {
                                    $post_type = '小队';
                                } elseif ($grade > 328) {
                                    $post_type = '分队';
                                } else {
                                    $post_type = '问题';
                                }
                            }
                        ?>
        	            {{$single['info'][$k]['customer_name']}}
                    </td>
                    <td align="center">
                        {{$post_type}}
                    </td>
        	     @endif

        	    <td align="right" title="去年累计: {{(int)$last_year_data[$k]}} - 今年累计: {{(int)$this_year_data[$k]}}">
        	         @if($last_year_data[$k] > 0)
                        <span @if(($v / $last_year_data[$k] - 1) < 0) style="color:red;" @endif>
                        <?php 
                            $last_year_pre = number_format(($v / $last_year_data[$k] - 1) * 100, 2);
                        ?>
                        {{$last_year_pre}}%
                        </span>
        	         @else
        	            去年同期无
        	         @endif
        	    </td>

                <td align="right" title="去年累计: {{(int)$last_year_data[$k]}} - 今年累计: {{(int)$this_year_data[$k]}}">
                    <span @if($growth['a'][$k] < 0) style="color:red;" @endif>
                        {{number_format($growth['a'][$k], 2)}}
                    </span>
               </td>

        	    <td align="right">{{number_format($this_year_data[$k], 2)}}</td>

                <td align="center">{{number_format(($v / array_sum($this_year_data) * 100), 2)}}%</td>

                <td align="center">{{number_format(($growth['a'][$k] / array_sum($this_year_data) * 100), 2)}}%</td>

                <td align="center">
                    <?php 
                        if ($growth['a'][$k] > 0) {
                            echo number_format(($growth['a'][$k] / $growth_b) * 100, 2);
                        } else {
                            echo '-'.number_format(($growth['a'][$k] / $growth_c) * 100, 2);
                        }
                    ?>%
                </td>

        	    <?php $category_money = $categorys['money'][$now_year][$k]; ?>
                 @foreach($categorys['name'] as $category)
        			<td align="right">{{number_format($category_money[$category['id']],2)}}</td>
                    <?php $total[$category['id']] += $category_money[$category['id']]; ?>
        		 @endforeach
        	</tr>
        	@endforeach
            <tr>
                <th align="center">净值合计</th>
                <th align="center"></th>
                
                @if($select['query']['tag'] == 'customer_id')
                    <th align="center"></th>
                    <th align="center"></th>
                    <th align="center"></th>
                @endif

                <th align="center"></th>
                <th align="right">
                    <?php
                        $v2 = $this_year_sum - $last_year_sum;
                        if ($last_year_sum > 0) {
                            $pre = number_format(($v2 / $last_year_sum) * 100, 2);
                        } 
                    ?>
                    <span @if($pre < 0) style="color:red;" @endif>{{$pre}}%</span>
                </th>

                <th align="right">
                    {{number_format($growth_a, 2)}}
                </th>
                
                <th align="right">{{number_format($total['all'], 2)}}</th>

                <th align="center"></th>
                <th align="center"></th>
                <th align="center"></th>
                @foreach($categorys['name'] as $category)
                    <th align="right">{{number_format($total[$category['id']],2)}}</th>
                @endforeach
            </tr>
            <tr>
                <th align="center">增长合计</th>
                <th align="center"></th>
                
                @if($select['query']['tag'] == 'customer_id')
                    <th align="center"></th>
                    <th align="center"></th>
                    <th align="center"></th>
                @endif

                <th align="center"></th>
                <th align="right">
                    <?php 
                        if ($last_year_sum > 0) {
                            $pre = number_format(($growth_b / $last_year_sum) * 100, 2);
                        }
                    ?>
                    <span @if($pre < 0) style="color:red;" @endif>{{$pre}}%</span>
                </th>
                <th align="right">
                    {{number_format($growth_b, 2)}}
                </th>
                <th align="center"></th>
                
                
                <th align="right"></th>
                <th align="center"></th>
                <th align="center"></th>
                @foreach($categorys['name'] as $category)
                    <th align="right"></th>
                @endforeach
            </tr>

            <tr>
                <th align="center">下降合计</th>
                <th align="center"></th>
                
                @if($select['query']['tag'] == 'customer_id')
                    <th align="center"></th>
                    <th align="center"></th>
                    <th align="center"></th>
                @endif

                <th align="center"></th>
                <th align="right">
                    <?php 
                        if ($last_year_sum > 0) {
                            $pre = number_format(($growth_c / $last_year_sum) * 100, 2);
                        }
                    ?>
                    <span @if($pre < 0) style="color:red;" @endif>{{$pre}}%</span>
                </th>
                <th align="right">
                    {{number_format($growth_c, 2)}}
                </th>
                <th align="center"></th>
                <th align="center"></th>
                <th align="center"></th>
                
                <th align="right"></th>
                @foreach($categorys['name'] as $category)
                    <th align="right"></th>
                @endforeach
            </tr>

    </table>

</div>