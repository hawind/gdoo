<select class="form-control input-sm" id='region1_id' name='region1_id' data-toggle="redirect" data-url="{{$query}}">
<option value="0">大区</option>
    @if(count($select['region1']))
    @foreach($select['region1'] as $k => $v)
    <option value="{{$v['id']}}" @if($select['query']['region1_id']==$v['id']) selected @endif>{{$v['name']}}</option>
    @endforeach 
    @endif
</select>

<select class="form-control input-sm" id='region2_id' name='region2_id' data-toggle="redirect" data-url="{{$query}}">
<option value="0">省区</option>
@if(count($select['region2']))
@foreach($select['region2'] as $k => $v)
<option value="{{$v['id']}}" @if($select['query']['region2_id']==$v['id']) selected @endif>{{$v['name']}}</option>
@endforeach
@endif
</select>

<select class="form-control input-sm" id='region3_id' name='region3_id' data-toggle="redirect" data-url="{{$query}}">
  <option value="0">区域</option>
   @if(count($select['region3']))
   @foreach($select['region3'] as $k => $v)
    <option value="{{$v['id']}}" @if($select['query']['region3_id']==$v['id']) selected @endif>{{$v['name']}}</option>
   @endforeach 
   @endif
</select>

<select class="form-control input-sm" id='customer_id' name='customer_id' data-toggle="redirect" data-url="{{$query}}">
  <option value="0">客户</option>
   @if(count($select['customer'])) 
   @foreach($select['customer'] as $k => $v)
    <option value="{{$v['id']}}" @if($v['status'] == 0) style="color:#f00;" @endif @if($select['query']['customer_id'] == $v['id']) selected @endif>{{$v['customer_name']}}</option>
   @endforeach 
   @endif
</select>

@if(isset($select['query']['customer_type']))
<select class="form-control input-sm" id='customer_type' name='customer_type' data-toggle="redirect" data-url="{{$query}}">
<option value="0">客户类型</option>
    @if(count($customer_type)) 
    @foreach($customer_type as $k => $v)
    <option value="{{$v['id']}}" @if($select['query']['customer_type'] == $v['id']) selected @endif>{{$v['name']}}</option>
    @endforeach 
    @endif
</select>
@endif

@if($select['query']['date1'])
&nbsp;
日期
<input class="form-control input-sm" data-toggle="date" value="{{$select['query']['date1']}}" size="13" name="date1" id="date1">
@endif
@if($select['query']['date2'])
-
<input class="form-control input-sm" data-toggle="date" value="{{$select['query']['date2']}}" size="13" name="date2" id="date2">
@endif