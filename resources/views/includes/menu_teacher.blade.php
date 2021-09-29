<div id="menu">
    <div class="menu-section">
        <div class="menu-title">
            {{ __('Calendar') }}
        </div>

        <div class="menu-item {{(isset($menu_active) && $menu_active=="classes")?"active":""}}">
            <a href="{{route("teacher_classes")}}">
                {{ __('Scheduled Classes') }}
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="students")?"active":""}}">
            <a href="{{route("students")}}">
                {{ __('My Students') }}
            </a>
        </div>
        <div class="menu-item {{(isset($menu_active) && $menu_active=="history_classes")?"active":""}}">
            <a href="{{route("teacher_history_classes")}}">
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
            <a href="{{route("logout")}}">
                Logout
            </a>
        </div>
    </div>

</div>