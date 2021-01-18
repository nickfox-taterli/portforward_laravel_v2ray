@extends('layouts.default')

@section('content')
  <div class="jumbotron">
    <h1>免费转发服务</h1>
    <p class="lead">
      本转发服务仅用于测试,每次申请可以用7天,仅用于测试用途,所有转发都会被公开.
    </p>
    <p>
      注意:7天后转发会自动删除,同时支持TCP/UDP,但UDP限速10Mbps.
    </p>
    <p>
      <a class="btn btn-lg btn-success" href="{{ route('create') }}" role="button">现在注册</a>
    </p>
  </div>
@stop