<div class="panel" style="box-shadow:none">
	<div class="panel-heading b-b b-light text-center">
		<h4 class="m-xs m-l-none">{{$row['name']}}</h4>
		<small class="text-muted">位置: {{$row['location']}}</small>
	</div>
	<div class="panel-body text-base">
		<div class="table-responsive">
		<table class="m-b-none">
			@foreach($row['images'] as $image)
				<tr>
					<td>
						<div class="text-center"><img src="{{$upload_url}}/{{$image['path']}}" max-width="100%" /></div>
					</td>
				</tr>
			@endforeach
		</table>
		</div>
	</div>
</div>