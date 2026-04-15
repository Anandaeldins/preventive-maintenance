@extends('layouts.bar')

@section('content')
    <h2>Dashboard Teknisi</h2>

    <p>Total Task: {{ $totalTask }}</p>
    <p>Done: {{ $doneTask }}</p>
    <p>Pending: {{ $pendingTask }}</p>
    <p>High Risk: {{ $highRisk }}</p>

    <hr>

    <h4>Task List</h4>

    @foreach ($tasks as $t)
        <p>{{ $t->tanggal_task }} - {{ $t->status }}</p>
    @endforeach
@endsection
