<div class="navbar">
    <a data-toggle="modal" data-target="#aside" class="navbar-item pull-left hidden-lg-up">
        <i class="material-icons">&#xe5d2;</i>
    </a>

    <div class="navbar-item pull-left h5" id="pageTitle"></div>
    <ul class="nav navbar-nav pull-right">
        <li class="nav-item dropdown">
            <a class="nav-link clear" href data-toggle="dropdown">
                <span class="avatar">
                     @if(isset($user_data->user_avatar))
                        <img src="{{ url('uploads/avatar/thumb_'.$user_data->user_avatar) }}" alt="User Image">
                    @else
                        <img src="{{ url('uploads/avatar/user.png') }}" alt="User Image"/>
                    @endif
                    <i class="bottom"></i>
                </span>
            </a>

            <div class="dropdown-menu pull-right dropdown-menu-scale">
               @if (Sentinel::inRole('admin'))
                    <a href="{{url('setting')}}"
                       class="dropdown-item"><span>{{trans('left_menu.settings')}}</span></a>
                @endif
                <a href="{{url('profile')}}" class="dropdown-item"><span>{{trans('left_menu.profile')}}</span></a>
                <a href="{{url('logout')}}" class="dropdown-item">{{trans('left_menu.logout')}}</a></div>
        </li>
        <li class="nav-item hidden-md-up">
            <a class="nav-link" data-toggle="collapse" data-target="#collapse">
                <i class="material-icons">&#xe5d4;</i>
            </a>
        </li>
    </ul>
    <div class="collapse navbar-toggleable-sm" id="collapse">
        <ul class="nav navbar-nav">
            @if (Sentinel::inRole('customer'))
            <li class="nav-item dropdown">
                <a class="nav-link" href="{{url('support')}}">
                    <i class="fa fa-envelope"></i> <span>{{trans('left_menu.support')}}</span></a>
            </li>
            @endif
            @if (Sentinel::inRole('admin') || Sentinel::inRole('staff'))
                <li class="nav-item dropdown">
                    <a class="nav-link" href="{{url('mailbox')}}">
                        <i class="fa fa-envelope-o"></i> <span>{{trans('left_menu.email')}}</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="{{url('sales_order')}}">
                        <i class="material-icons">attach_money</i>
                        <span>{{trans('left_menu.sales_order')}}</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="{{url('invoice')}}">
                        <i class="material-icons">web</i>
                        <span>{{trans('left_menu.invoices')}}</span></a>
                </li>
            @endif
            @if (Sentinel::inRole('admin'))
            <li class="nav-item dropdown">
                <a class="nav-link" href="{{url('setting')}}">
                    <i class="fa fa-cog"></i> <span>{{trans('left_menu.settings')}}</span></a>
            </li>
            @endif
            </ul>
    </div>
</div>