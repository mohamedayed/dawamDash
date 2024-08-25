@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Shifts ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Shifts</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add or Update</h3>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

        <div class="card-body">

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Name</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('name', $data->name, array('class' => 'form-control', 'required')) }}
                    <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Shift name</p>
                </div>
            </div>
 	 	 	<div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Start Time</strong>
                </div>
                <div class="col-sm-4 col-content">
                    {{ Form::text('start_time', $data->start_time, array('class' => 'form-control timepicker', 'required')) }}
                    <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> <b>(Format: Hour:Minute)</b> Fill with the start time hour office</p>
                </div>
            </div>
 	 	 	<div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">End Time</strong>
                </div>
                <div class="col-sm-4 col-content">
                    {{ Form::text('end_time', $data->end_time, array('class' => 'form-control timepicker', 'required')) }}
                    <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> <b>(Format: Hour:Minute)</b> Fill with the end time hour office</p>
                </div>
            </div>
 	 	 	<div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Late Mark After (in minutes)</strong>
                </div>
                <div class="col-sm-4 col-content">
                    {{ Form::text('late_mark_after', $data->late_mark_after, array('class' => 'form-control timepicker_minutes', 'required')) }}
                    <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> <b>(Format: Hour:Minute)</b> <b>(in minutes)</b> How many minutes is said to be late</p>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Color</strong>
                </div>
                <div class="col-sm-4 col-content">
                    <select id="color" name="color" class="form-control">
                        @php
                            $colors = ['chartreuse', 'cyan', 'LightPink', 'yellow', 'snow'];
                        @endphp
                        @foreach ($colors as $color)
                    
                        <option value="{{ $color }}" {{ $color == $data->color ? "selected" : "" }}>{{ ucfirst($color) }}</option>
                        @endforeach
                       
                    </select>
                    <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Choose color. Color will show in Attendance page. So you can see their shift from the color</p>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/jquery-timepicker/jquery.timepicker.css') }}">
@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>
    <script src="{{ asset('vendor/jquery-timepicker/jquery.timepicker.js') }}"></script>
    <script src="{{ asset('js/backend/shifts/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
