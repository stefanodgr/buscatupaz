<div id="menu">
    <div class="menu-section">
        <div class="menu-item {{(isset($menu_active) && $menu_active=="dashboard")?"active":(!isset($menu_active)?"active":"")}}">
            <a href="{{route("dashboard")}}">
                Dashboard
            </a>
        </div>
    </div>

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

        <div class="menu-item {{(isset($menu_active) && $menu_active=="electives")?"active":""}}">
            <a href="{{route("electives")}}">
                Electives
            </a>
        </div>

    </div>

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

    <div class="menu-section">
        <div class="menu-title">
            Information
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="rankings")?"active":""}}">
            <a href="{{route("admin_teacher_statistics")}}">
                Rankings
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

        @if($user->hasRole("student"))
            <div class="menu-item">
                <a href="{{route("change_rol",["rol_name"=>"student"])}}">
                    Student
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

        <div class="menu-item {{(isset($menu_active) && $menu_active=="refer")?"active":""}}">
            <a href="{{route("referral_page")}}">
                Get Free Time
            </a>
        </div>
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
        <div class="menu-item">
            <a href="{{route("logout")}}">
                Logout
            </a>
        </div>
    </div>

</div>