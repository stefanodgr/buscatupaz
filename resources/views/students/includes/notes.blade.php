<div class="teachers-container">
    @if(count($notes)>0)
        @foreach($notes as $note)
            <div class="progress-container progress-container-half">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="container-fluid">
                                <div class="progress-container-title progress-container-title-left">
                                    <h1>{{ __('Teacher') }}</h1>
                                </div>
                                <div class="progress-container-desc progress-container-desc-left">
                                    <p>
                                        {{$note->teacher->first_name}} {{$note->teacher->last_name}}
                                    </p>
                                </div>
                                <div class="progress-container-title progress-container-title-left">
                                    <h1>{{ __('Publication date') }}</h1>
                                </div>
                                <div class="progress-container-desc progress-container-desc-left">
                                    <p>
                                        @if(DateTime::createFromFormat("Y-m-d H:i:s",$note->created_at)->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")) 

                                            {{ __('Today at') }} {{DateTime::createFromFormat("Y-m-d H:i:s",$note->created_at)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}

                                        @elseif(DateTime::createFromFormat("Y-m-d H:i:s",$note->created_at)->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")==DateTime::createFromFormat("Y-m-d",gmdate("Y-m-d"))->setTimezone(new DateTimeZone($user->timezone))->sub(new DateInterval('P1D'))->format("Y-m-d"))

                                            Yesterday at {{DateTime::createFromFormat("Y-m-d H:i:s",$note->created_at)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                                        
                                        @else
                                            {{DateTime::createFromFormat("Y-m-d H:i:s",$note->created_at)->setTimezone(new DateTimeZone($user->timezone))->format("Y-m-d")}} at {{DateTime::createFromFormat("Y-m-d H:i:s",$note->created_at)->setTimezone(new DateTimeZone($user->timezone))->format("h:ia")}}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="progress-container-desc">
                                <div class="progress-container-title progress-container-title-left">
                                    <h1>{{ __('Note') }}</h1>
                                </div>
                                <div class="progress-container-desc progress-container-desc-left">
                                    @if($note->teacher->id==$user->id)
                                        <textarea rows="4" class="form-control" placeholder="{{ __('Enter a note if you wish') }}" id="{{$note->id}}">{{$note->description}}</textarea>
                                        <div style="text-align:center;">
                                            <button style="margin-top:15px; display:none;" type="button" id="button-{{$note->id}}" class="btn btn-primary"><i class="fa fa-refresh"></i></button>
                                        </div>
                                    @else
                                        <p>{{$note->description}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $("#{{$note->id}}").focus(function() {
                    $("#button-{{$note->id}}").show();
                });
                $("#{{$note->id}}").focusout(function() {
                    $.post("{{route("teacher_update_note")}}", {
                        "_token": "{{csrf_token()}}",
                        "note_id": "{{$note->id}}",
                        "description": $(this).val(),
                    }, function (data) {
                        $("#button-{{$note->id}}").hide();
                    });
                });
            </script>
        @endforeach
    @endif

    @if($showMore)
        <div class="load-more-students">
            Show 5 more <i class="fa fa-angle-down" aria-hidden="true"></i>
        </div>
    @endif
</div>
