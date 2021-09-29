@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item" href="{{route("admin_levels")}}">
                    Levels <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
                <a class="breadcrumb-item">
                    Edit <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_levels_trash",["level_id"=>$level->id])}}" class="btn btn-default">{{ __('Delete') }}</a>
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

            <h1>Edit Level</h1>



            <form action="{{route("admin_levels_update")}}" method="post" >
                {{ csrf_field() }}
                <input type="hidden" name="level_id" value="{{$level->id}}"/>

                <div class="admin-section">
                    <div class="admin-section-desc">
                        <p>{{ __('Basic Info') }}</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Nombre</label>
                                <input class="form-control" value="{{$level->name}}" placeholder="Nombre" name="name" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Slug</label>
                                <input class="form-control" value="{{$level->slug}}" placeholder="Slug" name="slug"/>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">

                            <div class="col-xs-12 col-sm-12">
                                <label>Type</label>
                                <select class="form-control" name="type">
                                    <option value="real" {{$level->type=="real"?"selected":""}}>Real</option>
                                    <option value="intros" {{$level->type=="intros"?"selected":""}}>DELE - Intro</option>
                                    <option value="grammar" {{$level->type=="grammar"?"selected":""}}>DELE - Grammar</option>
                                    <option value="skills" {{$level->type=="skills"?"selected":""}}>DELE - Skills Improvement</option>
                                    <option value="test" {{$level->type=="test"?"selected":""}}>DELE - Test-Prep</option>
                                    <option value="elective" {{$level->type=="elective"?"selected":""}}>Elective</option>
                                    <option value="sm" {{$level->type=="sm"?"selected":""}}>Grammarless</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Order</label>
                                <input type="number" class="form-control" value="{{$level->level_order}}" placeholder="Orden" name="level_order" required/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Status</label>
                                <div>
                                    <input type="checkbox" class="checkbox-switch" {{$level->enabled?"checked":""}} name="enabled">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12">
                                <label>Description</label>
                                <textarea id="text" class="form-control" name="meta_description">{{$level->meta_description}}</textarea>
                            </div>
                        </div>
                    </div>


                </div>

                <div class="admin-section {{$level->type=="elective"?"active":""}}" id="elective-options">
                    <div class="admin-section-desc">
                        <p>Elective Options</p>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Youtube Link</label>
                                <input class="form-control" value="{{$level->youtube_link}}" placeholder="Video" name="youtube_link"/>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>Price</label>
                                <input class="form-control" placeholder="Price" name="price" value="{{$level->price}}" required/>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Sales Description</label>
                                <textarea id="text" class="form-control" name="desc_sales">{{$level->desc_sales}}</textarea>
                            </div>
                            <div class="col-xs-12 col-sm-6">
                                <label>What's Included</label>
                                <textarea id="text" class="form-control" name="desc_included">{{$level->desc_included}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6">
                                <label>Who is the X Elective For?</label>
                                <textarea id="text" class="form-control" name="desc_whofor">{{$level->desc_whofor}}</textarea>
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
            $('textarea#value,textarea#text,textarea#desc_included,textarea#desc_sales').redactor({
                minHeight: 300,
                convertDivs: false,
                tabindex: 4,
                buttons: buttons
            })

        })(jQuery);

    </script>
@endsection