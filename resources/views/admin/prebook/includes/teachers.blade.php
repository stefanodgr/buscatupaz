@if(count($teachers)>0)
	@foreach($teachers as $key => $teacher)
		<div class="classes-confirm">
		    <div class="class-confirm">
		    	<img src="{{asset("assets/users/photos/".$teacher->id.".jpg?v=".rand())}}" alt="{{$teacher->first_name}}" />
		        <div class="teacher_name">
		            {{$teacher->first_name}} <span class="teacher_email">Zoom: {{$teacher->zoom_email}}</span>
		        </div>
		        <div class="teacher_time">
		        	Total hours: {{$teacher->hours}} | Busy hours: {{$teacher->busy}} | Hours available for prebooks: {{$teacher->percentage}}
		        </div>
		    </div>
		</div>
	@endforeach
@endif