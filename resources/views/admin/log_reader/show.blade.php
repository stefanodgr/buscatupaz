@extends("layouts.main")

@section("content")
    <div class="main-content-wrapper">
    	@php
    		$text="";
    		$path="../storage/logs/laravel-".gmdate("Y-m-d").".log";
    		if(file_exists($path)){
		        $fp = fopen($path, "r");
		        while(!feof($fp)){
		            $line = fgets($fp);
		            $text.=$line."\n";
		        }
		        fclose($fp);	
    		}
            else{
                $text="The selected .log file does not exist.";
            }
    	@endphp

    	<h1>Log Reader</h1>

        <div class="container-fluid">
            <div class="row" style="text-align: center;">
                <div>
                    <label>Date</label>
                    <input style="margin:auto; width: 200px; text-align:center;" class="form-control" data-toggle="datepicker" id="date" placeholder="Select a date" value="{{gmdate("Y-m-d")}}" readonly>
                </div>
            </div>
        </div>
        <br>
        <div class="container-fluid">
            <div calss="row">
                <div class="col-xs-12 col-sm-12">
                    <textarea class="form-control" rows="12" id="text" disabled>{{$text}}</textarea>
                </div>
            </div>
        </div>
    </div>

@endsection

@section("scripts")
    <script>
        $(document).ready(function () {
            $('[data-toggle="datepicker"]').datepicker({
                format: 'yyyy-mm-dd'
            });

            $('#date').change(function () {
                var route = '/admin/log_reader/'+$(this).val();
                $.get(route,{'_token':'{{csrf_token()}}'}, 
                function (data) {
                	$('#date').datepicker('hide');

                	if(data.text==0) {
                		$('#text').val("The selected .log file does not exist.");
                	}
                	else {
                		$('#text').val(data.text);
                	}
                });
            });
        })
    </script>
@endsection