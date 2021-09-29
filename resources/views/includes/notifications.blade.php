<div class="notify-container">
    <div class="notify-container-wrapper">
        @if(session('message_info'))
            <div class="notify-item notify-info">
                <div class="notify-icon">
                    <i class="fas fa-frown-open"></i>
                </div>
                <div class="notify-message">
                    <div class="notify-item-title">Info:</div>
                    {!! session('message_info')  !!}
                </div>
                <div class="notify-close">
                    Close
                </div>
            </div>
        @endif
        @if($errors->any())
            @foreach ($errors->all() as $error)
                <div class="notify-item notify-error">
                    <div class="notify-icon">
                        <i class="fas fa-frown-open"></i>
                    </div>
                    <div class="notify-message">
                        <div class="notify-item-title">Error:</div>
                        {!! $error  !!}
                    </div>
                    <div class="notify-close">
                        Close
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>



