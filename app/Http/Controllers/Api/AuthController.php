<?php

namespace App\Http\Controllers\Api;

use App\Models\Email;
use App\Models\InviteUser;
use App\Models\User;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Reminder;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use JWTAuth;
use Sentinel;
use Illuminate\Support\Facades\Mail;

/**
 * Auth routes
 *
 * @Resource("Auth", uri="/")
 */
class AuthController extends Controller {
  /**
   * Check whether its a CRM installation or not
   *
   * @get("/")
   * @versions({"v1"})
   * @Request()
   * @Response(200,body={"success":"CRM system"}
   */
  public function crmCheck() {
    return response()->json(["success" => "CRM system"], 200);
  }

  /**
   * Login to system
   *
   * @Post("/login")
   * @Versions({"v1"})
   * @Transaction({
   *  @Request({"email": "admin@mw.life","password": "bar"}),
   *  @Response(200, body={
  "token": "token",
  "user": {
  "id": 4,
  "first_name": "Admin",
  "last_name": "Doe",
  "email": "admin@mw.life",
  "phone_number": "465465415",
  "user_id": "1",
  "user_avatar": "image.jpg",
  "permissions" : "{sales_team.read:true,sales_team.write:true,sales_team.delete:true,leads.read:true,leads.write:true,leads.delete:true,opportunities.read:true,opportunities.write:true,opportunities.delete:true,logged_calls.read:true,logged_calls.write:true,logged_calls.delete:true,meetings.read:true,meetings.write:true,meetings.delete:true,products.read:true,products.write:true,products.delete:true,quotations.read:true,quotations.write:true,quotations.delete:true,sales_orders.read:true,sales_orders.write:true,sales_orders.delete:true,invoices.read:true,invoices.write:true,invoices.delete:true,pricelists.read:true,pricelists.write:true,pricelists.delete:true,contracts.read:true,contracts.write:true,contracts.delete:true,staff.read:true,staff.write:true,staff.delete:true}",
  },
  "role": "user",
  "date_format": "2017-10-10",
  "time_format": "10:15",
  "date_time_format": "2017-10-10 10:15"
   *   }),
   *   @Response(401, body={
  "error": "invalid_credentials"
   *   }),
   *   @Response(500, body={
  "error": "could_not_create_token"
   *   })
  })
   */
  public function login(Request $request) {
    // grab credentials from the request
    $credentials = $request->only('email', 'password');
    try {
      // attempt to verify the credentials and create a token for the user
      if (!$token = JWTAuth::attempt($credentials)) {
        return response()->json(['error' => 'invalid_credentials'], 401);
      }
    } catch (JWTException $e) {
      // something went wrong whilst attempting to encode the token
      return response()->json(['error' => 'could_not_create_token'], 500);
    }
    // all good so return the data
    Sentinel::authenticate($request->only('email', 'password'), $request['remember-me']);
    $user = Sentinel::getUser();
    if ($user->inRole('admin')) {
      $role = 'admin';
    } elseif ($user->inRole('user')) {
      $role = 'user';
    } elseif ($user->inRole('staff')) {
      $role = 'staff';
    } elseif ($user->inRole('customer')) {
      $role = 'customer';
    } else {
      $role = 'no_role';
    }
    $user = User::select('id', 'first_name', 'last_name', 'email', 'phone_number', 'user_id', 'user_avatar')->find(Sentinel::getUser()->id);

    $permissions = User::find(Sentinel::getUser()->id)->getPermissions();

    return response()->json(['token' => $token,
      'user' => $user,
      'role' => $role,
      'date_format' => Settings::get('date_format'),
      'time_format' => Settings::get('time_format'),
      'date_time_format' => Settings::get('date_format') . ' ' . Settings::get('time_format'),
      'permissions' => $permissions], 200);
  }

  /**
   * Edit profile
   *
   * @Post("/edit_profile")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo", "first_name":"First","last_name":"Last", "phone_number":"+356421544","email":"email@email.com", "password":"password", "password_confirmation":"password","avatar":"base64_encoded_image"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function editProfile(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'phone_number' => $request->input('phone_number'),
      'email' => $request->input('email'),
      'password' => $request->input('password'),
    );
    $rules = array(
      'first_name' => 'required',
      'last_name' => 'required',
      'phone_number' => 'required',
      'email' => 'required',
      'password' => 'same:password_confirmation',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $user = User::find($this->user->id);
      if ($request->password != "") {
        $user->password = bcrypt($request->password);
      }
      if (!is_null($request->avatar)) {
        $output_file = uniqid() . ".jpg";
        $ifp = fopen(public_path() . '/uploads/avatar/' . $output_file, "wb");
        fwrite($ifp, base64_decode($request->avatar));
        fclose($ifp);
        $user->user_avatar = $output_file;
      }
      $user->phone_number = $request->phone_number;
      $user->update($request->except('token', 'password', 'avatar', 'password_confirmation'));

      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Create profile from staff invite
   *
   * @Post("/create_profile_invite")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"first_name":"First","last_name":"Last", "phone_number":"+356421544","password":"password", "code":"invite_code"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function createProfileInvite(Request $request) {
    $data = array(
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'phone_number' => $request->input('phone_number'),
      'password' => $request->input('password'),
      'code' => $request->input('code'),
    );
    $rules = array(
      'first_name' => 'required',
      'last_name' => 'required',
      'phone_number' => 'required',
      'password' => 'required',
      'code' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $user = User::where('email', $request->email)->first();
      $inviteUser = InviteUser::where('email', $request->email)
        ->where('code', $request->code)->first();
      if (!is_null($user) || !is_null($inviteUser)) {
        return response()->json(['error' => "not_valid_data"], 500);
      }
      $staff = Sentinel::registerAndActivate(
        array(
          'first_name' => $request->first_name,
          'last_name' => $request->last_name,
          'email' => $inviteUser->email,
          'password' => $request->password,
        )
      );
      $role = Sentinel::findRoleBySlug('staff');
      $role->users()->attach($staff);

      $user = User::find($staff->id);
      $user->phone_number = $request->phone_number;
      $user->save();

      $inviteUser->claimed_at = Carbon::now();
      $inviteUser->save();

      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Create profile from staff invite
   *
   * @Post("/update_password")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"code": "foo", "id":1, "password":"password"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   */

  public function updatePassword(Request $request) {
    $data = array(
      'code' => $request->input('code'),
      'id' => $request->input('id'),
      'password' => $request->input('password')
    );
    $rules = array(
      'code' => 'required',
      'id' => 'required',
      'password' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $user = Sentinel::findById($request->id);
      $reminder = Reminder::exists($user, $request->code);
      //incorrect info was passed.
      if ($reminder == false) {
        return response()->json(['error' => 'not_valid_data'], 500);
      }
      Reminder::complete($user, $request->code, $request->password);

      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Get all email
   *
   * @Get("/emails")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo"}),
   *      @Response(200, body={
  "emails": {
  {
  "get_emails": {
  {
  "id": 14,
  "assign_customer_id": 0,
  "to": "1",
  "from": "1",
  "subject": "dfgdfg",
  "message": "dfgfdg",
  "read": 0,
  "delete_sender": 0,
  "delete_receiver": 0,
  "created_at": "2017-06-23 11:05:46",
  "updated_at": "2017-06-23 11:05:46",
  "deleted_at": null,
  "sender": {
  "id": 1,
  "email": "admin@mw.life",
  "last_login": "2017-06-23 14:02:43",
  "first_name": "Admin",
  "last_name": "Admin",
  "phone_number": null,
  "user_avatar": null,
  "user_id": 1,
  "created_at": "2017-03-02 16:09:12",
  "updated_at": "2017-06-23 14:02:43",
  "deleted_at": null,
  "full_name": "Admin Admin",
  "avatar": "http://localhost:81/crm54/public/uploads/avatar/user.png"
  }
  }
  },
  "sent_emails": {
  {
  "id": 14,
  "assign_customer_id": 0,
  "to": "1",
  "from": "1",
  "subject": "dfgdfg",
  "message": "dfgfdg",
  "read": 0,
  "delete_sender": 0,
  "delete_receiver": 0,
  "created_at": "2017-06-23 11:05:46",
  "updated_at": "2017-06-23 11:05:46",
  "deleted_at": null,
  "receiver": {
  "id": 1,
  "email": "admin@mw.life",
  "last_login": "2017-06-23 14:02:43",
  "first_name": "Admin",
  "last_name": "Admin",
  "phone_number": null,
  "user_avatar": null,
  "user_id": 1,
  "created_at": "2017-03-02 16:09:12",
  "updated_at": "2017-06-23 14:02:43",
  "deleted_at": null,
  "full_name": "Admin Admin",
  "avatar": "http://localhost:81/crm54/public/uploads/avatar/user.png"
  }
  }
  }
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function emails(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $get_emails = Email::with('sender')->where('to', $this->user->id)->where('delete_receiver', 0)->orderBy('id', 'desc')->get();
    $sent_emails = Email::with('receiver')->where('from', $this->user->id)->where('delete_sender', 0)->orderBy('id', 'desc')->get();

    return response()->json(['get_emails' => $get_emails, 'sent_emails' => $sent_emails], 200);
  }

  /**
   * Get single email
   *
   * @Get("/email")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo","email_id":"1"}),
   *      @Response(200, body={
  "email": {
  "id": 1,
  "assign_customer_id": 0,
  "to": "1",
  "from": "1",
  "subject": "dfgdfg",
  "message": "dfgfdg",
  "read": 1,
  "delete_sender": 0,
  "delete_receiver": 0,
  "created_at": "2017-06-23 11:05:46",
  "updated_at": "2017-06-23 14:34:56",
  "deleted_at": null,
  "sender": {
  "id": 1,
  "email": "admin@mw.life",
  "last_login": "2017-06-23 14:02:43",
  "first_name": "Admin",
  "last_name": "Admin",
  "phone_number": null,
  "user_avatar": null,
  "user_id": 1,
  "created_at": "2017-03-02 16:09:12",
  "updated_at": "2017-06-23 14:02:43",
  "deleted_at": null,
  "full_name": "Admin Admin",
  "avatar": "http://localhost:81/crm54/public/uploads/avatar/user.png"
  }
  }
  }),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function email(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'email_id' => $request->input('email_id')
    );
    $rules = array(
      'email_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes() && $this->user) {
      $email = Email::with('sender')->find($request->email_id);
      $email->read = 1;
      $email->save();
      return response()->json(['email' => $email], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Post email
   *
   * @Post("/post_email")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","message":"This is message","recipients":{1,2,3},"subject":"Email subject"}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   **/

  public function postEmail(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'recipients' => $request->input('recipients'),
      'subject' => $request->input('subject'),
      'message' => $request->input('message')
    );
    $rules = array(
      'recipients' => 'required',
      'subject' => 'required',
      'message' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $request->merge(['user_id' => $this->user->id]);
      if (!empty($request->recipients)) {
        foreach ($request->recipients as $item) {
          if ($item != "0" && $item != "") {
            $email = new Email($request->only('subject', 'message'));
            $email->to = $item;
            $email->from = $this->user->id;
            $email->save();

            $user = User::find($item);

            if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
              Mail::send('emails.contact', array(
                'user' => $user->first_name . ' ' . $user->last_name,
                'bodyMessage' => $request->message
              ),
                function ($m)
                use ($user, $request) {
                  $m->from(Settings::get('site_email'), Settings::get('site_name'));
                  $m->to($user->email)->subject($request->subject);
                });
            }
          }
        }
      }
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Delete email
   *
   * @Post("/delete_email")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"token": "foo", "email_id":"1"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function deleteEmail(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'email_id' => $request->input('email_id'),
    );
    $rules = array(
      'email_id' => 'required|integer',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $mail = Email::find($request->email_id);
      if ($mail->to == $this->user->id) {
        $mail->delete_receiver = 1;
      } else {
        $mail->delete_sender = 1;
      }
      $mail->save();
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

  /**
   * Replay email
   *
   * @Post("/replay_email")
   * @Versions({"v1"})
   * @Transaction({
   *       @Request({"token": "foo","message":"This is message", "email_id":1}),
   *       @Response(200, body={"success":"success"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *    })
   * })
   **/

  public function replayEmail(Request $request) {
    $this->user = JWTAuth::parseToken()->authenticate();
    $data = array(
      'email_id' => $request->input('email_id'),
      'message' => $request->input('message')
    );
    $rules = array(
      'email_id' => 'required',
      'message' => 'required',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $email_orig = Email::find($request->only('email_id'))->first();
      if (!is_null($email_orig)) {
        $request->merge(['subject' => 'Re: ' . $email_orig->subject]);
        $email = new Email($request->only('message', 'subject'));
        $email->to = $email_orig->from;
        $email->from = $this->user->id;
        $email->save();

        $user = User::find($email_orig->from);

        if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
          Mail::send('emails.contact', array(
            'user' => $user->first_name . ' ' . $user->last_name,
            'bodyMessage' => $request->message
          ),
            function ($m)
            use ($user, $request) {
              $m->from(Settings::get('site_email'), Settings::get('site_name'));
              $m->to($user->email)->subject($request->subject);
            });
        }
      }
      return response()->json(['success' => "success"], 200);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }


  /**
   * Password recovery
   *
   * @Post("/password_recovery")
   * @Versions({"v1"})
   * @Transaction({
   *      @Request({"email":"admin@sms.com"}),
   *      @Response(200, body={"success":"success"}),
   *      @Response(201, body={"error":"user_dont_exists"}),
   *      @Response(500, body={"error":"not_valid_data"})
   *       })
   * })
   */
  public function passwordRecovery(Request $request) {
    $data = array(
      'email' => $request->input('email'),
    );
    $rules = array(
      'email' => 'required|email',
    );
    $validator = Validator::make($data, $rules);
    if ($validator->passes()) {
      $userFind = User::where('email', $request->email)->first();
      if (isset($userFind->id)) {
        $user = Sentinel::findById($userFind->id);
        ($reminder = Reminder::exists($user)) || ($reminder = Reminder::create($user));

        $data = [
          'email' => $user->email,
          'name' => $userFind->full_name,
          'subject' => trans('auth.reset_your_password'),
          'code' => $reminder->code,
          'id' => $user->id
        ];
        Mail::send('emails.reminder', $data, function ($message) use ($data) {
          $message->to($data['email'], $data['name'])->subject($data['subject']);
        });
        return response()->json(['success' => "success"], 200);
      }
      return response()->json(['error' => "user_dont_exists"], 201);
    } else {
      return response()->json(['error' => 'not_valid_data'], 500);
    }
  }

}
