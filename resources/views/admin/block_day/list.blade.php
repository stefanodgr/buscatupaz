@extends("layouts.main")

@section("content")
    @if(isset($breadcrumb))
        <div class="imagina-breadcrumb">
            <div class="breadcrumb-wrapper">
                <a class="breadcrumb-item">
                    Blocked Days <i class="fa fa-angle-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="breadcrumb-actions">
                <a href="{{route("admin_block_day_create")}}" class="btn btn-default">Create</a>
                <a id="delete_button" class="btn btn-default">{{ __('Delete') }}</a>
                <a href="{{route("admin_block_day_logs")}}" class="btn btn-default">Audit Logs</a>
            </div>
        </div>
    @endif

    <div class="main-content-wrapper {{isset($breadcrump)?"main-content-wrapper-breadcrumb":""}}" id="admin-list">

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="bs-callout bs-callout-danger">
                    <h4>Error</h4>
                    {!!$error!!}
                </div>
            @endforeach
        @endif

        @if(session('message_info'))
            <div class="bs-callout bs-callout-info">
                <h4>Info</h4>
                {{session('message_info')}}
            </div>
        @endif
        
        <div class="bs-callout bs-callout-info" style="display: none;">
            <h4>Info</h4>
            <p class="success_msg"></p>
        </div>
        <table id="table-list" class="display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>{{ __('Teacher') }}</th>
                    <th>Blocking Day</th>
                    <th>From (UTC)</th>
                    <th>Till (UTC)</th>
                    <th></th>
                    <th><input type="checkbox" id="check_all" style="margin-left: -12px;"></th>
                </tr>
            </thead>
        </table>

    </div>

    <div id="block-day-confirm" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="link-diff">
                     Are you sure you want to Delete the selected blocked day(s)?
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="instant instant-option">
                    <button id="check_all_data" class="btn btn-primary btn-block">Confirm</button>
                    </div>
                    <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section("scripts")
    <script>
        $(document).ready(function() {
            $('#table-list').DataTable( {
                "ajax": '{{route("get_admin_block_day")}}',
                "columnDefs": [{ "orderable": false, "targets": -1 }],
                "iDisplayLength": 50,
                "language": {
                    "lengthMenu": "Show _MENU_ Blocked Days",
                    "info": "Showing _START_ to _END_ of _TOTAL_ Blocked Days",
                    "search":"search:"
                }
            });

            $('#check_all').on('click', function(e) {
            if($(this).is(':checked',true))  
            {
                $(".checkbox").prop('checked', true);  
            } else {  
                $(".checkbox").prop('checked',false);  
            }  
            });
            $('.checkbox').on('click',function(){
                if($('.checkbox:checked').length == $('.checkbox').length){
                    $('#check_all').prop('checked',true);
                }else{
                    $('#check_all').prop('checked',false);
                }
            });

            $("#delete_button").click(function(){
            $("#block-day-confirm").modal("show");
            });
        });
    </script>

    <script>
     $(document).on('click', '#check_all_data', function(){
        var id = [];
            $('.student_checkbox:checked').each(function(){
                id.push($(this).val());
            });

            if(id.length > 0)
            {
                $.ajax({
                    url:"{{ route('deleteall')}}",
                    method:"get",
                    data:{id:id},
                    success:function(data)
                    {
                       $('#block-day-confirm').modal('hide');
                       $('#table-list').DataTable().ajax.reload();
                       $('.bs-callout-info').show();
                       $("p.success_msg").html("Blocked Day deleted");
                    }
                });
            }
            else
            {
                alert("Please select atleast one blocked day to delete");
            }
    });
    </script>
    
@endsection