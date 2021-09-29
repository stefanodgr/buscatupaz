<div id="menu">
    <div class="menu-section">
        <div class="menu-item {{(isset($menu_active) && $menu_active=="dashboard")?"active":(!isset($menu_active)?"active":"")}}">
            <a href="{{route("admin_dashboard")}}">
                Dashboard
            </a>
        </div>
    </div>

    <div class="menu-section">
        <div class="menu-title">
            Users
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="users")?"active":""}}">
            <a href="{{route("admin_users")}}">
                Users
            </a>
        </div>
    </div>

    <div class="menu-section">
        <div class="menu-title">
            Lessons
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="levels")?"active":""}}">
            <a href="{{route("admin_levels")}}">
                Levels
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="lessons")?"active":""}}">
            <a href="{{route("admin_lessons")}}">
                Lessons
            </a>
        </div>
    </div>

    <div class="menu-section">
        <div class="menu-title">
            Information
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="classes")?"active":""}}">
            <a href="{{route("admin_classes")}}">
                {{ __('Classes') }}
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="prebook")?"active":""}}">
            <a href="{{route("admin_prebooks")}}">
                Prebooks
            </a>
        </div>
        <!--div class="menu-item {{(isset($menu_active) && $menu_active=="free_days")?"active":""}}">
            <a href="{{route("admin_free_days")}}">
                Free Days
            </a>
        </div-->
        <div class="menu-item {{(isset($menu_active) && $menu_active=="cancellations")?"active":""}}">
            <a href="{{route("admin_cancellations")}}">
                Cancellations
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="inmersions")?"active":""}}">
            <a href="{{route("admin_inmersions")}}">
                Immersions
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="rankings")?"active":""}}">
            <a href="{{route("admin_teacher_statistics")}}">
                Rankings
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="feedback")?"active":""}}">
            <a href="{{route("admin_feedback")}}">
                Feedback
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="locations")?"active":""}}">
            <a href="{{route("admin_locations")}}">
                Locations
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="block_day")?"active":""}}">
            <a href="{{route("admin_block_day")}}">
                Blocked Days
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="information_contents")?"active":""}}">
            <a href="{{route("admin_information_contents")}}">
                Information Contents
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
    </div>

    <div class="menu-section">
        <div class="menu-title">
            OTHER
        </div>

        @if($user->hasRole("teacher"))
            <div class="menu-item">
                <a href="{{route("change_rol",["rol_name"=>"teacher"])}}">
                    {{ __('Teacher') }}
                </a>
            </div>
        @endif

        @if($user->hasRole("student"))
            <div class="menu-item">
                <a href="{{route("change_rol",["rol_name"=>"student"])}}">
                    Student
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

        <div class="menu-item">
            <a href="https://baselang.com/support/">
                Help
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="log_reader")?"active":""}}">
            <a href="{{route("admin_get_log_reader")}}">
                Log Reader
            </a>
        </div>
        <div class="menu-item">
            <a href="{{route("logout")}}">
                Logout
            </a>
        </div>
    </div>

</div>