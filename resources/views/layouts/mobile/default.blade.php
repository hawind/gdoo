@include('layouts/mobile/header')

<div class="content m-n">

    @if(Session::has('message'))
    <div class="m-sm alert alert-success alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        {{Session::pull('message')}}
    </div>
    @endif

    @if(Session::has('error'))
    <div class="m-sm alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        {{Session::pull('error')}}
    </div>
    @endif

    @if($errors->has())
    <div class="m-sm alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        @foreach($errors->all() as $message)
        <div>{{$message}}</div>
        @endforeach
    </div>
    @endif

    <div class="content-body">
        {{$content}}
    </div>

</div>

</body>
</html>