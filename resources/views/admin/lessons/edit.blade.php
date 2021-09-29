@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_lessons")}}">
                    Lessons <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Edit <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_lessons_trash",["lesson_id"=>$lesson->id])}}" class="btn btn-default">{{ __('Delete') }}</a>
                <a href="{{route("admin_lessons")}}" class="btn btn-default">Cancel</a>
            </div>
        </div>

        <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="edit_action">

            @if($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="bs-callout bs-callout-danger">
                        <h4>Error</h4>
                        {!! $error !!}
                    </div>
                @endforeach
            @endif


            @if (session('message_info'))
                <div class="bs-callout bs-callout-info">
                    <h4>Info</h4>
                    {{ session('message_info') }}
                </div>
            @endif

            <h1>Edit Lesson</h1>



            <form action="{{route("admin_lessons_update")}}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <input type="hidden" name="lesson_id" value="{{$lesson->id}}"/>

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Name</label>
                                <input class="form-control" value="{{$lesson->name}}" placeholder="Name" name="name" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Slug</label>
                                <input class="form-control" value="{{$lesson->slug}}" placeholder="Slug" name="slug"/>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Level</label>
                                <select class="form-control" name="level_id">
                                    <option>N.A</option>
                                    @foreach($levels as $level)
                                        <option value="{{$level->id}}" {{$level->id==$lesson->level_id?"selected":""}}>{{$level->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Status</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" {{$lesson->enabled?"checked":""}} name="enabled">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Homework Audio</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" {{$lesson->homework_audio?"checked":""}} name="homework_audio">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Homework Text</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" {{$lesson->homework_text?"checked":""}} name="homework_text">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Free</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" name="is_free" {{$lesson->is_free?"checked":""}}>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Order</label>
                                <input type="number" class="form-control" value="{{$lesson->order}}" placeholder="Order" name="order" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>External URL</label>
                                <input class="form-control" value="{{$lesson->externalurl}}" placeholder="External URL" name="externalurl" />
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">

                            <div class="col-xs-12 col-sm-12">
                                <label>PDF</label>
                                <input type="file" name="lesson_pdf" accept="application/pdf" />
                            </div>
                            <div class="col-xs-12 col-sm-12">
                                @if(file_exists("assets/lessons/pdf/".$lesson->id.".pdf"))
                                    <div class="lesson-pdf">

                                        <iframe src="{{asset("library/pdf/index.html")}}#{{asset("assets/lessons/pdf/".$lesson->id.".pdf?".$lesson->updated_at->format("U"))}}"></iframe>
                                    </div>
                                    <div class="lesson-pdf-actions">
                                        <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#remopdfmodal">{{ __('Delete') }}</button>
                                    </div>

                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Description</label>
                                <textarea id="text" class="form-control" placeholder="Descripción" name="description">{{$lesson->description}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin_actions">
                    <button class="btn btn-primary" id="delete_button">{{ __('Save') }}</button>
                    <a class="btn btn-default" href="{{route("admin_lessons")}}">Cancel</a>
                </div>
            </form>
        </div>

        @if(file_exists("assets/lessons/pdf/".$lesson->id.".pdf"))
            <div id="remopdfmodal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <form action="{{route("admin_remove_pdf")}}" method="post">
                            {{ csrf_field() }}

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title">Remove PDF</h4>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="lesson_id" value="{{$lesson->id}}">
                                Are you sure you want to continue?
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-primary">Confirm</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        @endif

    @endif
@endsection

@section("scripts")

    <script type="text/javascript" src="{{asset("js/redactor.js")}}"></script>

    <script>
        $(document).ready(function() {
            $(".checkbox-switch").bootstrapSwitch();
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });


            (function($) {

                // Redactor buttons
                var buttons = [
                    'html',
                    '|', 'formatting',
                    '|', 'bold', 'italic', 'deleted',
                    '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent',
                    '|', 'video', 'file', 'table', 'link',
                    '|', 'fontcolor', 'backcolor',
                    '|', 'alignment',
                    '|', 'horizontalrule'
                ];

                // Instantiate redactor
                $('textarea#value').redactor({
                    minHeight: 300,
                    convertDivs: false,
                    tabindex: 4,
                    buttons: buttons
                });

                // Instantiate redactor
                $('textarea#text').redactor({
                    minHeight: 300,
                    convertDivs: false,
                    tabindex: 4,
                    buttons: buttons
                });

            })(jQuery);

        });
    </script>
@endsection