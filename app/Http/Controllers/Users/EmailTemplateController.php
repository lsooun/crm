<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\EmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Http\Request;
use Sentinel;

class EmailTemplateController extends UserController
{
    /**
     * @var EmailTemplateRepository
     */
    private $emailTemplateRepository;

    /**
     * EmailTemplateController constructor.
     * @param EmailTemplateRepository $emailTemplateRepository
     */
    public function __construct(EmailTemplateRepository $emailTemplateRepository)
    {
        parent::__construct();

        $this->emailTemplateRepository = $emailTemplateRepository;

        view()->share('type', 'email_template');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('email_template.email_templates');
        $emailTemplates = $this->emailTemplateRepository->getAll()
            ->get();

        return view('user.email_template.index', compact('title','emailTemplates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('email_template.new');
        return view('user.email_template.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmailTemplateRequest $request)
    {
        $this->emailTemplateRepository->create($request->all());

        return redirect("email_template");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EmailTemplate $emailTemplate
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        $title = trans('email_template.edit');
        return view('user.email_template.edit', compact('title', 'emailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function ajaxUpdate(Request $request, $emailTemplate)
    {
        $this->validate($request, [
            'title'             => 'required',
            'text'              => 'required',
        ]);

        $emailTemplate->update($request->only('title','text'));

        return response()->json(['message' => 'Updated successfully!', 'data' => compact('emailTemplate')], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($emailTemplate)
    {
    	$template = $emailTemplate;
        $emailTemplate->delete();
        return $template;
    }

    /**
     * Get ajax datatables data
     *
     */
    public function data()
    {
        $email_templates = $this->emailTemplateRepository->getAll()
            ->get()
            ->map(function ($email_template) {
                return [
                    'id' => $email_template->id,
                    'title' => $email_template->title,
                    'text' => $email_template->text,
                ];
            })->values();


        return response()->json(compact('email_templates'), 200);
    }

    public function ajaxGetTemplate(EmailTemplate $emailTemplate)
    {
        return EmailTemplate::find($emailTemplate->id);
    }


    public function postAjaxStore(EmailTemplateRequest $request)
    {
        $this->emailTemplateRepository->create($request->except('created', 'errors', 'selected'));

        return response()->json([], 200);
    }
}
