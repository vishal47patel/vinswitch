@if($response = session('response'))
@php
$page = request()->segment(count(request()->segments()));
$page_col = 6;
$page_col_offset = 6;
if($page == 'login'){
    $page_col_offset = 0;
    $page_col = 12;
}
@endphp
<div class="offset-md-{{$page_col_offset}} col-md-{{$page_col}} alert-absolute1">
    <div>
     
    <div class="alert alert-{{ $response['class'] }} alert-dismissible fade show alert-absolute" role="alert" id="list-msg">
        <strong>{{ $response['msg'] }}</strong>
    </div>
    </div>
</div>
<script>        
    setTimeout(function() {
        $('#list-msg').fadeOut('fast');
    }, 4000);
</script>
@endif
<div class="offset-md-6 col-md-6 alert-absolute">
    <div class="messages" id="ajax-msg"></div>
</div>
