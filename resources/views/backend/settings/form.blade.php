@extends('adminlte::page')
<!-- page title -->
@section('title', 'Settings | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Settings</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Update</h3>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'user_id')) }}

        <div class="card-body">
            <div class="row">
                <div class="col-md-7">
                    
                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">System Name</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('app_name', $data->app_name, array('class' => 'form-control', 'id' => 'app_name')) }}
                            <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Your System Name</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Color Theme</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            <select id="color" name="color" class="form-control">
                                @php
                                    $colors = ['blue', 'green', 'cyan', 'yellow', 'red', 'indigo', 'navy', 'purple', 'fuchsia', 'pink', 'maroon', 'orange', 'lime', 'teal', 'olive'];
                                @endphp
                                @foreach ($colors as $color)
                            
                                <option value="{{ $color }}" {{ $color == $data->color ? "selected" : "" }}>{{ ucfirst($color) }}</option>
                                @endforeach
                               
                            </select>
                            <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Choose color. Color will show in Attendance page. So you can see their shift from the color</p>
                        </div>
                    </div>

                    {{--  Logo  --}}
                    <div id="form-logo" class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Logo & Favicon</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            <input class="custom-file-input" name="logo" type="file"
                                accept="image/gif, image/jpeg,image/jpg,image/png" data-max-width="800"
                                data-max-height="400">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                            <span class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Please upload the logo <b>(Recommended size: 196px × 196px, max 5MB) and transparent background</b>.</span>
                            <div class="logo-preview-area">
                                <div id="logo_preview" class="logo-preview">
                                        <img src="{{ asset('img/'.$data->logo) }}" width="160" title="logo"
                                            class="img-circle elevation-2">
                                </div>
                                {{-- only logo has main logo, add css class "show" --}}
                                <p class="delete-logo-preview @if ($data->logo != null && $data->logo != 'logo.png') show @endif"
                                onclick="deleteLogoPreview(this);"><i class="fa fa-window-close"></i></p>
                                {{-- delete flag for already uploaded logo in the server --}}
                                <input name="logo_delete" type="hidden">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Copyright</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('copyright', $data->copyright, array('class' => 'form-control', 'id' => 'copyright')) }}
                            <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Copyright name</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Key App</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('key_app', $data->key_app, array('class' => 'form-control', 'required', 'id' => 'key', 'readonly')) }}
                            <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Application Key is used for communication with the Application. You can change the key by clicking on the button "Generate New Key" don't forget to save it</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Timezone</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::select('timezone', $timezone, $data->timezone, array('id' => 'timezone', 'class' => 'form-control select2')) }}
                            <p class="form-text text-muted"><i class="fa fa-question-circle" aria-hidden="true"></i> Fill in the Timezone you are</p>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <img class="img-responsive img-thumbnail" src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ $data->qr }}&choe=UTF-8" style="margin: 0 auto;display: block;">
                    <p class="text-center"><b><i class="fa fa-question-circle" aria-hidden="true"></i> QR Code</b></p>
                    <p class="text-center form-text text-muted">This QR code is used for the first time opening the App. <br>Scan this QR and this is done only once.</p>
                    <p class="text-center"><a href="https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl={{ $data->qr }}&choe=UTF-8" target="_blank"><button type="button" class="btn btn-success">Download</button></a></p>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>

                    <button type="button" id="generate-key" class="btn btn-primary">Generate New Key</button>
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
@stop

@section('js')
    <script src="{{ asset('js/backend/settings/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
