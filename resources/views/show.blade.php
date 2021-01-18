@extends('layouts.default')

@section('content')
<h1 class ="text-center">转发列表</h1>
<div class="container text-align:center" >
  <table class="table table-bordered">
    <tr>
      <th>主机IP</th>
      <th>主机端口</th>
      <th>源IP</th>
      <th>源端口</th>
      <th>创建时间</th>
    </tr>

    @foreach ($records as $record)
    <tr>
      <td>{{ $record->server }}</td>
      <td>{{ $record->dport }}</td>
      <td>{{ $record->client }}</td>
      <td>{{ $record->sport }}</td>
      <td>{{ $record->created_at }}</td>
      </tr>
    @endforeach
  </table>
</div>
<div class="d-flex justify-content-center">
    {!! $records->links('pagination::bootstrap-4') !!}
</div>
@stop

