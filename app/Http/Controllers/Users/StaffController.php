<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\InviteRequest;
use App\Http\Requests\StaffRequest;
use App\Mail\StaffInvite;
use App\Models\User;
use App\Repositories\InviteUserRepository;
use App\Repositories\UserRepository;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use Jenssegers\Date\Date;

class StaffController extends UserController {
  private $date_format = 'd-m-Y';
  private $emailSettings;
  private $siteNameSettings;
  /**
   * @var UserRepository
   */
  private $userRepository;
  /**
   * @var InviteUserRepository
   */
  private $inviteUserRepository;

  /**
   * StaffController constructor.
   * @param UserRepository $userRepository
   * @param InviteUserRepository $inviteUserRepository
   */
  public function __construct(UserRepository $userRepository,
                              InviteUserRepository $inviteUserRepository) {

    $this->middleware('authorized:staff.read', ['only' => ['index', 'data']]);
    $this->middleware('authorized:staff.write', ['only' => ['create', 'store', 'update', 'edit']]);
    $this->middleware('authorized:staff.delete', ['only' => ['delete']]);

    parent::__construct();
    $this->userRepository = $userRepository;
    $this->inviteUserRepository = $inviteUserRepository;
    $this->date_format = Settings::get('date_format');
    $this->emailSettings = Settings::get('site_email');
    $this->siteNameSettings = Settings::get('site_name');

    view()->share('type', 'staff');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {
    $title = trans('staff.staffs');
    return view('user.staff.index', compact('title'));

  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create() {
    $title = trans('staff.new');
    return view('user.staff.create', compact('title'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param StaffRequest|Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(StaffRequest $request) {
    if ($request->hasFile('user_avatar_file')) {
      $file = $request->file('user_avatar_file');
      $file = $this->userRepository->uploadAvatar($file);

      $request->merge([
        'user_avatar' => $file->getFileInfo()->getFilename(),
      ]);

      $this->generateThumbnail($file);
    }

    $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'email', 'password'));

    $role = Sentinel::findRoleBySlug('staff');
    $role->users()->attach($user);

    $user = User::find($user->id);

    $this->user->users()->save($user);

    foreach ($request->get('permissions', []) as $permission) {
      $user->addPermission($permission);
    }

    $user->phone_number = $request->phone_number;
    $user->user_avatar = $request->user_avatar;
    $user->save();

    return redirect("staff");
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param User $staff
   * @return \Illuminate\Http\Response
   * @internal param int $id
   */
  public function edit(User $staff) {
    if ($staff->id == '1') {
      return redirect('staff');
    } else {
      $title = trans('staff.edit');
      return view('user.staff.edit', compact('title', 'staff'));
    }
  }

  /**
   * Update the specified resource in storage.
   *
   * @param StaffRequest|Request $request
   * @param User $staff
   * @return \Illuminate\Http\Response
   * @internal param int $id
   */
  public function update(StaffRequest $request, User $staff) {
    if ($request->password != "") {
      $staff->password = bcrypt($request->password);
    }

    if ($request->hasFile('user_avatar_file')) {
      $file = $request->file('user_avatar_file');
      $file = $this->userRepository->uploadAvatar($file);

      $request->merge([
        'user_avatar' => $file->getFileInfo()->getFilename(),
      ]);

      $this->generateThumbnail($file);

    } else {
      $request->merge([
        'user_avatar' => $staff->user_avatar,
      ]);
    }

    foreach ($staff->getPermissions() as $key => $item) {
      $staff->removePermission($key);
    }

    foreach ($request->get('permissions', []) as $permission) {
      $staff->addPermission($permission);
    }

    $staff->first_name = $request->first_name;
    $staff->last_name = $request->last_name;
    $staff->phone_number = $request->phone_number;
    $staff->email = $request->email;
    $staff->user_avatar = $request->user_avatar;
    $staff->save();

    return redirect("staff");
  }

  public function show(User $staff) {
    $title = trans('staff.show_staff');
    $action = "show";
    return view('user.staff.show', compact('title', 'staff', 'action'));
  }

  public function delete(User $staff) {
    $title = trans('staff.delete_staff');
    return view('user.staff.delete', compact('title', 'staff'));
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  User $staff
   * @return \Illuminate\Http\Response
   */
  public function destroy(User $staff) {
    if ($staff->id != '1') {
      $staff->delete();
    }
    return redirect('staff');
  }


  public function data(Datatables $datatables) {
    $staffs = $this->userRepository->getAllNew()->with('staffSalesTeam')
      ->get()
      ->filter(function ($user) {
        return ($user->inRole('staff') && $user->id != $this->user->id);
      })->map(function ($user) {
        $created_date = new Date($user->created_at);
        return [
          'id' => $user->id,
          'full_name' => $user->full_name,
          'email' => $user->email,
          'created_at' => $created_date->format($this->date_format),
          'count_uses' => $user->staffSalesTeam->count()
        ];
      });

    return $datatables->collection($staffs)
      ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'staff.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'staff/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                     @endif
                                     <a href="{{ url(\'staff/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     @if(Sentinel::getUser()->hasAccess([\'staff.delete\']) || Sentinel::inRole(\'admin\') && $count_uses==0)
                                        <a href="{{ url(\'staff/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                      @endif')
      ->removeColumn('id')
      ->removeColumn('count_uses')
      ->escapeColumns(['actions'])->make();
  }

  /**
   * @param $file
   */
  private function generateThumbnail($file) {
    Thumbnail::generate_image_thumbnail(public_path() . '/uploads/avatar/' . $file->getFileInfo()->getFilename(),
      public_path() . '/uploads/avatar/' . 'thumb_' . $file->getFileInfo()->getFilename());
  }


  public function invite() {
    $title = trans('staff.invite_staffs');
    $date_format = Settings::get('date_format');
    return view('user.staff.invite', compact('title', 'date_format'));
  }

  public function inviteSave(InviteRequest $request) {
    $emails = explode(",", $request->emails);
    foreach ($emails as $email) {
      $validator = \Validator::make(
        ['individualEmail' => $email],
        array('individualEmail' => 'email')
      );

      if ($validator->passes() && is_null(User::where('email', $email)->first())) {
        $invite = $this->inviteUserRepository->create(['email' => trim($email)]);
        if (!filter_var($this->emailSettings, FILTER_VALIDATE_EMAIL) === false) {
          Mail::to($email)
            ->send(new StaffInvite($invite));
        }
      }
    }
    return redirect('staff/invite');
  }
}
