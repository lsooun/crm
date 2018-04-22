<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Http\Requests\MailboxRequest;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Sentinel;
use App\Http\Requests;

class MailboxController extends UserController
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EmailTemplateRepository
     */
    private $emailTemplateRepository;

    /**
     * @param UserRepository $userRepository
     * @param EmailTemplateRepository $emailTemplateRepository
     * @internal param CompanyRepository $
     */
    public function __construct(UserRepository $userRepository, EmailTemplateRepository $emailTemplateRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;

        view()->share('type', 'mailbox');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('mailbox.mailbox');


        return view('customers.mailbox.index', compact('title'));
    }

    public function getAllData()
    {
        $email_list = Email::where('to', $this->user->id)->where('delete_receiver',0)->orderBy('id','desc')->get();
        $sent_email_list = Email::where('from', $this->user->id)->where('delete_sender',0)->orderBy('id','desc')->get();

        $users = $this->userRepository->getUsersAndStaffs()
	        ->map(function ($user) {
		        return [
			        'id'   => $user->id,
			        'text' => $user->full_name.' ('. $user->email.')',
		        ];
	        })->values();

        $orig_users_list = $this->userRepository->getUsersAndStaffs()
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name.' ('. $user->email.')',
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) && $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
            $users_list=[];
        foreach($orig_users_list as $newuser)
        {
            $users_list[]=$newuser;
        }
        $have_email_template = false;
        return response()->json(compact('email_list', 'sent_email_list', 'users','users_list','have_email_template'), 200);
    }


    public function getMail($id)
    {
        $email = Email::with('sender')->find($id);
        $email->read = 1;
        $email->save();

        return response()->json(compact('email'), 200);
    }

    public function getSentMail($id)
    {
        $email = Email::with('receiver')->find($id);
        $email->save();

        return response()->json(compact('email'), 200);
    }

    public function getMailTemplate($id)
    {
        $template = EmailTemplate::find($id);

        return response()->json(compact('template'), 200);
    }

    function sendEmail(MailboxRequest $request)
    {
        $message_return = '<div class="alert alert-danger">' . trans('mailbox.danger') . '</div>';
        if (!empty($request->recipients)) {
            foreach ($request->recipients as $item) {
                if ($item != "0" && $item != "") {
                    $email = new Email($request->except('recipients','emailTemplate'));
                    $email->to = $item;
                    $email->from = $this->user->id;
                    $email->save();

                    $user = User::find($item);

                    if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
                        Mail::send('emails.contact', array('user' => $user->first_name . ' ' . $user->last_name, 'bodyMessage' => $request->message),
                            function ($m)
                            use ($user, $request) {
                                $m->from(Settings::get('site_email'), Settings::get('site_name'));
                                $m->to($user->email)->subject($request->subject);
                            });
                    }

                    $message_return = '<div class="alert alert-success">' . trans('mailbox.success') . '</div>';
                }

            }
        }
        echo $message_return;

    }

    function deleteMail(Email $mail)
    {
        if($mail->to == $this->user->id){
            $mail->delete_receiver= 1;
        }
        else{
            $mail->delete_sender= 1;
        }
        $mail->save();
    }


    public function postRead(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        $model = Email::find($request->get('id'));
        $model->read = true;
        $model->save();

        return response()->json(['message' => trans('mailbox.update_status')], 200);
    }


    public function getData()
    {
        $emails_list = Email::where('to', $this->user->id)
	        ->where('delete_receiver',0)
            ->where('read', 0)
            ->with('sender')
            ->orderBy('id', 'desc');

        $total = $emails_list->count();
        $emails = $emails_list->latest()->take(5)->get();

        return response()->json(compact('total', 'emails'), 200);
    }

    public function postMarkAsRead(Request $request)
    {
        if ($ids = $request->get('ids')) {
            if (is_array($ids)) {
                $messages = Email::whereIn('id', $ids)->get();
                foreach ($messages as $message) {
                    $message->read = true;
                    $message->save();
                }
            } else {
                $message = Email::find($ids);
                $message->read = true;
                $message->save();
            }
        }
    }

    public function getSent()
    {

        $sent = Email::where('from', $this->user->id)
	        ->where('delete_sender',0)
            ->with('receiver')
            ->orderBy('id', 'desc')->get();

        return response()->json(compact('sent'), 200);
    }

    public function getReceived(Request $request)
    {
        $received_list = Email::where('to', $this->user->id)
            ->where('delete_receiver',0)
            ->where('subject', 'like', '%' . $request->get('query', '') . '%')
            ->where('message', 'like', '%' . $request->get('query', '') . '%')
            ->with('sender');
        $received = $received_list->orderBy('id', 'desc')->get();
        $received_count = $received_list->count();
        return response()->json(compact('received','received_count'), 200);
    }


    public function postSend(Request $request)
    {
        foreach ($request->recipients as $item) {
            if ($item != "0" && $item != "") {
                $email = new Email($request->except('recipients', 'emailTemplate'));
                $email->to = $item;
                $email->from = \Sentinel::getUser()->id;
                $email->save();

                $user = User::find($item);

                if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
                    Mail::send('emails.contact', array('user' => $user->full_name, 'bodyMessage' => $request->message),
                        function ($m)
                        use ($user, $request) {
                            $m->from(Settings::get('site_email'), Settings::get('site_name'));
                            $m->to($user->email)->subject($request->subject);
                        });
                }
            }
        }

        return response()->json(['message' => 'Message sent successfully!'], 200);

    }

    public function postDelete(Request $request)
    {
        if ($ids = $request->get('ids')) {
            if (is_array($ids)) {
                $messages = Email::whereIn('id', $ids)->get();
                foreach ($messages as $message) {
                    $message->delete_receiver = 1;
                    $message->save();
                }
            } else {
                $message = Email::find($ids);
                $message->delete_sender = 1;
                $message->save();
            }
        }
    }

    public function postReply($id, Request $request)
    {
        $orgMail = Email::find($id);

        $request->merge([
            'subject' => 'Re: ' . $orgMail->subject,
        ]);

        $email = new Email($request->all());
        $email->to = $orgMail->from;
        $email->from = \Sentinel::getUser()->id;
        $email->save();

        $user = User::find($orgMail->from);


        if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            Mail::send('emails.contact', array('user' => $user->full_name, 'bodyMessage' => $request->message),
                function ($m)
                use ($user, $request) {
                    $m->from(Settings::get('site_email'), Settings::get('site_name'));
                    $m->to($user->email)->subject($request->subject);
                });
        }

    }

}
