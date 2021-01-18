@extends('layouts.default')
@section('title', '新建转发')

@section('content')
<script src="https://www.google.com/recaptcha/api.js"></script>
<script>
  function onSubmit(token) {
    document.getElementById("create").submit();
  }
</script>
<div class="offset-md-2 col-md-8">
  <div class="card ">
    <div class="card-header">
      <h5>新建转发</h5>
    </div>
    <div class="card-body">
      @include('shared._errors')
      <form id="create" method="POST" action="{{ route('create') }}">
          {{ csrf_field() }}

          <div class="form-group">
            <label for="server">服务器:</label>
            <select name="server">
              <option value="102.223.72.241">13.124.203.36 - CN2 GIA</option>
              <option value="54.178.78.36">54.178.78.36 - 日本东京</option>
              <option value="13.124.203.36">13.124.203.36 - 韩国首尔</option>
            </select>
          </div>

          <div class="form-group">
            <label for="dport">目标端口号:</label>
            <input type="number" name="dport" class="form-control" value="{{ old('dport') }}">
          </div>

          <div class="form-group">
            <label for="client">源服务器地址:</label>
            <input type="text" name="client" class="form-control" value="{{ old('client') }}">
          </div>

          <div class="form-group">
            <label for="sport">源服务器端口号:</label>
            <input type="text" name="sport" class="form-control" value="{{ old('sport') }}">
          </div>

          <button class="g-recaptcha"
            data-sitekey="6LfL5d4UAAAAAIk2NzC6pA_n5MesLMuwevh244Oq"
            data-callback='onSubmit'
            data-action='submit'>创建转发</button>
      </form>
    </div>
  </div>
</div>
@stop
