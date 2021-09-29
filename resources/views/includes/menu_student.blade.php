@if(isset($user) && $user->subscriptionAdquired() && ($user->subscriptionAdquired()->plan->name=="medellin_RW" || $user->subscriptionAdquired()->plan->name=="medellin_RW_1199" || $user->subscriptionAdquired()->plan->name=="medellin_RW_Lite" || $user->subscriptionAdquired()->plan->name=="medellin_DELE"))
    <div id="menu">
        <div class="menu-section">
            <div class="menu-item {{(isset($menu_active) && $menu_active=="dashboard")?"active":(!isset($menu_active)?"active":"")}}">
                <a href="{{route("dashboard")}}">
                    Dashboard
                </a>
            </div>
        </div>
{{--
        <div class="menu-section">
            <div class="menu-title">
                Lessons
            </div>
            @if(session("current_subscription")=="dele")
                <div class="menu-item {{(isset($menu_active) && $menu_active=="intros")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"intros"])}}">
                        Intros
                    </a>
                </div>
                <div class="menu-item {{(isset($menu_active) && $menu_active=="grammar")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"grammar"])}}">
                        Grammar
                    </a>
                </div>
                <div class="menu-item {{(isset($menu_active) && $menu_active=="skills")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"skills"])}}">
                        Skills Improvement
                    </a>
                </div>
                <div class="menu-item {{(isset($menu_active) && $menu_active=="test")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"test"])}}">
                        Test-Prep
                    </a>
                </div>
            @else
                <div class="menu-item {{(isset($menu_active) && $menu_active=="core_lessons")?"active":""}}">
                    <a href="{{route("lessons")}}">
                        Core Lessons
                    </a>
                </div>
                @if($user->isInmersionStudent())
                    <div class="menu-item {{(isset($menu_active) && $menu_active=="sm_lessons")?"active":""}}">
                        <a href="{{route("sm_lessons")}}">
                            Grammarless Lessons
                        </a>
                    </div>
                @endif
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="electives")?"active":""}}">
                <a href="{{route("electives")}}">
                    Electives
                </a>
            </div>
            @if($user->isInmersionStudent() && $user->isInmersionStudent()->location_id!=2)
                <div class="menu-item {{(isset($menu_active) && $menu_active=="city_information")?"active":""}}">
                    <a href="{{route("city_information")}}">
                        City Information
                    </a>
                </div>
            @endif
        </div>
--}}
        <div class="menu-section">
            <div class="menu-title">
                {{ __('Calendar') }}
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes_new")?"active":""}}">
                <a href="{{route("classes_new")}}">
                    Book Online Class
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes_in_person_new")?"active":""}}">
                <a href="{{route("classes_in_person_new")}}">
                    Book In-Person Class
                </a>
            </div>
            @if(!isset($location) || !$location || ($location && $location->id!=2))
            <div class="menu-item {{(isset($menu_active) && $menu_active=="teachers")?"active":""}}">
                <a href="{{route("teachers")}}">
                    Online Teachers
                </a>
            </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="teachers_school")?"active":""}}">
                <a href="{{route("teachers_school")}}">
                    @if(!isset($location) || !$location || ($location && $location->id!=2)) Medellin @endif Teachers
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes")?"active":""}}">
                <a href="{{route("classes")}}">
                    {{ __('Scheduled Classes') }}
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="history_classes")?"active":""}}">
                <a href="{{route("history_classes")}}">
                    {{ __('Class History') }}
                </a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-title">
                {{ __('My profile') }}
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="basic_info")?"active":""}}">
                <a href="{{route("profile")}}">
                    {{ __('Basic Info') }}
                </a>
            </div>
            {{--
            <div class="menu-item {{(isset($menu_active) && $menu_active=="progress")?"active":""}}">
                <a href="{{route("profile_progress")}}">
                    {{ __('My Progress') }}
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="billing")?"active":""}}">
                <a href="{{route("billing")}}">
                    Billing
                </a>
            </div>
            --}}
        </div>


        <div class="menu-section">
            <div class="menu-title">
                OTHER
            </div>
            @if($user->hasRole("admin"))
                <div class="menu-item">
                    <a href="{{route("change_rol",["rol_name"=>"admin"])}}">
                        Admin
                    </a>
                </div>
            @endif
            @if($user->hasRole("teacher"))
                <div class="menu-item">
                    <a href="{{route("change_rol",["rol_name"=>"teacher"])}}">
                        {{ __('Teacher') }}
                    </a>
                </div>
            @endif
            @if($user->hasRole("coordinator"))
                <div class="menu-item">
                    <a href="{{route("change_rol",["rol_name"=>"coordinator"])}}">
                        Coordinator
                    </a>
                </div>
            @endif
            @if(session("impersonated_by"))
                <div class="menu-item">
                    <a href="{{route("stop_impersonate")}}">
                        Stop Impersonate
                    </a>
                </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="feedback")?"active":""}}">
                <a href="{{route("get_feedback")}}">
                    Feedback
                </a>
            </div>
            <div class="menu-item">
                <a href="https://baselang.com/support/">
                    Help
                </a>
            </div>
            <div class="menu-item">
                <a href="{{route("logout")}}">
                    Logout
                </a>
            </div>
        </div>
    </div>

@elseif($user->isInmersionStudent() && $user->isInmersionStudent()->location_id!=2)
    <div id="menu">
        <div class="menu-section">
            <div class="menu-title">
                Lessons
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="city_information")?"active":""}}">
                <a href="{{route("city_information")}}">
                    City Information
                </a>
            </div>
            @if($user->isInmersionRunning())
            <div class="menu-item {{(isset($menu_active) && $menu_active=="sm_lessons")?"active":""}}">
                <a href="{{route("sm_lessons")}}">
                    Grammarless Lessons
                </a>
            </div>
            @endif
        </div>

        @if($user->isInmersionRunning())
        <div class="menu-section">
            <div class="menu-title">
                {{ __('Calendar') }}
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes_new")?"active":""}}">
                <a href="{{route("classes_new")}}">
                    {{ __('Book New Class') }}
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes")?"active":""}}">
                <a href="{{route("classes")}}">
                    {{ __('Scheduled Classes') }}
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="teachers")?"active":""}}">
                <a href="{{route("teachers")}}">
                    {{ __('Teachers') }}
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="history_classes")?"active":""}}">
                <a href="{{route("history_classes")}}">
                    {{ __('Class History') }}
                </a>
            </div>
        </div>
        @endif

        <div class="menu-section">
            <div class="menu-title">
                {{ __('My profile') }}
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="basic_info")?"active":""}}">
                <a href="{{route("profile")}}">
                    {{ __('Basic Info') }}
                </a>
            </div>
            @if($user->isInmersionRunning())
            <div class="menu-item {{(isset($menu_active) && $menu_active=="progress")?"active":""}}">
                <a href="{{route("profile_progress")}}">
                    {{ __('My Progress') }}
                </a>
            </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="billing")?"active":""}}">
                <a href="{{route("billing")}}">
                    Billing
                </a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-title">
                OTHER
            </div>
            @if($user->hasRole("admin"))
                <div class="menu-item">
                    <a href="{{route("change_rol",["rol_name"=>"admin"])}}">
                        Admin
                    </a>
                </div>
            @endif
            @if($user->hasRole("teacher"))
                <div class="menu-item">
                    <a href="{{route("change_rol",["rol_name"=>"teacher"])}}">
                        {{ __('Teacher') }}
                    </a>
                </div>
            @endif
            @if($user->hasRole("coordinator"))
                <div class="menu-item">
                    <a href="{{route("change_rol",["rol_name"=>"coordinator"])}}">
                        Coordinator
                    </a>
                </div>
            @endif
            @if(session("impersonated_by"))
                <div class="menu-item">
                    <a href="{{route("stop_impersonate")}}">
                        Stop Impersonate
                    </a>
                </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="feedback")?"active":""}}">
                <a href="{{route("get_feedback")}}">
                    Feedback
                </a>
            </div>
            <div class="menu-item">
                <a href="https://baselang.com/support/">
                    Help
                </a>
            </div>
            <div class="menu-item">
                <a href="{{route("logout")}}">
                    Logout
                </a>
            </div>
        </div>

    </div>
@else
    <div id="menu">
        <div class="menu-section">
            <div class="menu-item {{(isset($menu_active) && $menu_active=="dashboard")?"active":(!isset($menu_active)?"active":"")}}">
                <a href="{{route("dashboard")}}">
                    Dashboard
                </a>
            </div>
        </div>
{{--
        <div class="menu-section">
            <div class="menu-title">
                Lessons
            </div>
            @if(session("current_subscription")=="dele")
                <div class="menu-item {{(isset($menu_active) && $menu_active=="intros")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"intros"])}}">
                        Intros
                    </a>
                </div>
                <div class="menu-item {{(isset($menu_active) && $menu_active=="grammar")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"grammar"])}}">
                        Grammar
                    </a>
                </div>
                <div class="menu-item {{(isset($menu_active) && $menu_active=="skills")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"skills"])}}">
                        Skills Improvement
                    </a>
                </div>
                <div class="menu-item {{(isset($menu_active) && $menu_active=="test")?"active":""}}">
                    <a href="{{route("lessons_type",["type"=>"test"])}}">
                        Test-Prep
                    </a>
                </div>
            @else
                <div class="menu-item {{(isset($menu_active) && $menu_active=="core_lessons")?"active":""}}">
                    <a href="{{route("lessons")}}">
                        Core Lessons
                    </a>
                </div>
            @endif
            @if($user->isInmersionStudent())
            <div class="menu-item {{(isset($menu_active) && $menu_active=="sm_lessons")?"active":""}}">
                <a href="{{route("sm_lessons")}}">
                    Grammarless Lessons
                </a>
            </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="electives")?"active":""}}">
                <a href="{{route("electives")}}">
                    Electives
                </a>
            </div>
            @if($user->isInmersionStudent() && $user->isInmersionStudent()->location_id!=2)
                <div class="menu-item {{(isset($menu_active) && $menu_active=="city_information")?"active":""}}">
                    <a href="{{route("city_information")}}">
                        City Information
                    </a>
                </div>
            @endif
        </div>
--}}
        @if(!(($user->isInmersionStudent() && $user->isInmersionStudent()->location_id!=2) || ($user->isInmersionStudent() && $user->isInmersionStudent()->location_id==2 && !$user->isOnlineInmersionStarted())))
        <div class="menu-section">
            <div class="menu-title">
                {{ __('Calendar') }}
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes_new")?"active":""}}">
                <a href="{{route("classes_new")}}">
                    {{ __('Book New Class') }}
                </a>
            </div>
            @if($user->active_locations && gmdate("Y-m-d") >= $user->active_locations->date_to_schedule)
                <div class="menu-item {{(isset($menu_active) && $menu_active=="classes_in_person_new")?"active":""}}">
                    <a href="{{route("classes_in_person_new")}}">
                        Book In-Person Class
                    </a>
                </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="classes")?"active":""}}">
                <a href="{{route("classes")}}">
                    {{ __('Scheduled Classes') }}
                </a>
            </div>
            @if(count($user->buy_prebooks)>0)
                <div class="menu-item {{(isset($menu_active) && $menu_active=="prebook")?"active":""}}">
                    <a href="{{route("prebook")}}">
                        Prebook
                    </a>
                </div>
            @endif
            @if($user->getCurrentSubscription() && $user->getCurrentSubscription()->plan->name=="baselang_hourly")
                <div class="menu-item {{(isset($menu_active) && $menu_active=="credits")?"active":""}}">
                    <a href="{{route("credits")}}">
                        Get Credits
                    </a>
                </div>
            @endif
            <div class="menu-item {{(isset($menu_active) && $menu_active=="teachers")?"active":""}}">
                <a href="{{route("teachers")}}">
                    {{ __('Teachers') }}
                </a>
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="history_classes")?"active":""}}">
                <a href="{{route("history_classes")}}">
                    {{ __('Class History') }}
                </a>
            </div>
        </div>
        @endif

        <div class="menu-section">
            <div class="menu-title">
                {{ __('My profile') }}
            </div>
            <div class="menu-item {{(isset($menu_active) && $menu_active=="basic_info")?"active":""}}">
                <a href="{{route("profile")}}">
                    {{ __('Basic Info') }}
                </a>
            </div>
        {{--
        <div class="menu-item {{(isset($menu_active) && $menu_active=="progress")?"active":""}}">
            <a href="{{route("profile_progress")}}">
                {{ __('My Progress') }}
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="billing")?"active":""}}">
            <a href="{{route("billing")}}">
                Billing
            </a>
        </div>
        --}}
</div>

<div class="menu-section">
    <div class="menu-title">
        OTHER
    </div>
    @if($user->hasRole("admin"))
        <div class="menu-item">
            <a href="{{route("change_rol",["rol_name"=>"admin"])}}">
                Admin
            </a>
        </div>
    @endif
    @if($user->hasRole("teacher"))
        <div class="menu-item">
            <a href="{{route("change_rol",["rol_name"=>"teacher"])}}">
                {{ __('Teacher') }}
            </a>
        </div>
    @endif
    @if($user->hasRole("coordinator"))
        <div class="menu-item">
            <a href="{{route("change_rol",["rol_name"=>"coordinator"])}}">
                Coordinator
            </a>
        </div>
    @endif
    @if(session("impersonated_by"))
        <div class="menu-item">
            <a href="{{route("stop_impersonate")}}">
                Stop Impersonate
            </a>
        </div>
    @endif
    @if($user->showGetFreeTime())
        <div class="menu-item {{(isset($menu_active) && $menu_active=="refer")?"active":""}}">
            <a href="{{route("referral_page")}}">
                Get Free Time
            </a>
        </div>
    @endif
    <div class="menu-item {{(isset($menu_active) && $menu_active=="feedback")?"active":""}}">
        <a href="{{route("get_feedback")}}">
            Feedback
        </a>
    </div>
    <div class="menu-item">
        <a href="https://baselang.com/support/">
            Help
        </a>
    </div>
    <div class="menu-item">
        <a href="{{route("logout")}}">
            Logout
        </a>
    </div>
</div>

</div>
@endif
