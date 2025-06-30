@extends('layout')

@section('content')
<h2>All Reports</h2>
<ul>
@foreach ($reports as $r)
  <li>
    <a href="{{ url($r['slug'].'.html') }}">{{ $r['title'] }}</a>
    <small>({{ date('Y-m-d', $r['mtime']) }})</small>
  </li>
@endforeach
</ul>
@endsection

