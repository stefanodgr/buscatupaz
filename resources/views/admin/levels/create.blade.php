@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_levels")}}">
                    Levels <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Create <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_levels")}}" class="btn btn-default">Cancel</a>
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

            <h1>Create Level</h1>



            <form action="{{route("admin_levels_create")}}" method="post" >
                {{ csrf_field() }}


                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Name</label>
                                <input class="form-control" value="" placeholder="Name" name="name" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Slug</label>
                                <input class="form-control" value="" placeholder="Slug" name="slug"/>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Type</label>
                                <select class="form-control" name="type" id="type_select">
                                    <option value="real">Real</option>
                                    <option value="intros">DELE - Intro</option>
                                    <option value="grammar">DELE - Grammar</option>
                                    <option value="skills">DELE - Skills Improvement</option>
                                    <option value="test">DELE - Test-Prep</option>
                                    <option value="elective">Elective</option>
                                    <option value="sm">Grammarless</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">

                            <div class="col-xs-12 col-sm-6">
                                <label>Order</label>
                                <input type="number" class="form-control" value="" placeholder="Orden" name="level_order" required/>
                            </div>

                            <div class="col-xs-12 col-sm-6">
                                <label>State</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" name="enabled" checked>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Description</label>
                                <textarea id="text" class="form-control" name="meta_description"></textarea>
                            </div>
                        </div>
                    </div>


                </div>


                <div class="admin-section" id="elective-options">
                    <div class="admin-section-desc">
                        <p>Elective Options</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Youtube Link</label>
                                <input class="form-control" value="" placeholder="Video" name="youtube_link"/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Price</label>
                                <input class="form-control" placeholder="Price" name="price" value="0" required/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin_actions">
                    <button class="btn btn-primary" id="delete_button">{{ __('Save') }}</button>
                    <a class="btn btn-default" href="{{route("admin_levels")}}">Cancel</a>
                </div>

            </form>
        </div>

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

            $("#type_select").change(function () {
                if($(this).val()=="elective"){
                    $("#elective-options").addClass("active");
                } else {
                    $("#elective-options").removeClass("active");
                }
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