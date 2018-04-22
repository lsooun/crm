<?php

namespace App\Http\Controllers\Customer;

use App\Events\Meeting\MeetingCreated;
use App\Http\Controllers\UserController;
use App\Http\Requests\MeetingRequest;
use App\Models\Customer;
use App\Models\Meeting;
use App\Models\Opportunity;
use App\Models\Option;
use App\Models\Salesteam;
use App\Models\User;
use App\Repositories\CompanyRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OptionRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Sentinel;
use Illuminate\Http\Request;
use App\Http\Requests;
use Yajra\Datatables\Datatables;

class MeetingController extends UserController
{
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    public function __construct()
    {

        parent::__construct();

        view()->share('type', 'customers/meeting');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $title = trans('meeting.meetings');

        return view('customers.meeting.index', compact('title'));
    }

    public function show(Meeting $meeting){
        $meeting_id = $meeting->id;
        $meeting = Meeting::where([
            ['company_attendees',Sentinel::getUser()->id],
            ['id',$meeting_id]
        ])->first();
        $title = trans('meeting.show');
        $user = User::all();
        $action = 'show';
        return view('customers.meeting.show', compact('title', 'meeting','user','action'));
    }
    public function delete(Meeting $meeting)
    {
        $meeting_id = $meeting->id;
        $meeting = Meeting::where([
            ['company_attendees',Sentinel::getUser()->id],
            ['id',$meeting_id]
        ])->first();
        $title = trans('meeting.delete');
        $user = User::all();
        return view('customers.meeting.delete', compact('title', 'meeting','user'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Meeting $meeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return redirect('meeting');
    }

    public function data(Datatables $datatables)
    {
        $meetings = Meeting::where('company_attendees',Sentinel::getUser()->id)->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : '',
                ];
            });
        return $datatables->collection($meetings)
            ->addColumn('actions', '
                                     @if(Sentinel::getUser()->hasAccess([\'meetings.delete\']) || Sentinel::inRole(\'admin\')|| Sentinel::inRole(\'customer\'))
                                     <a href="{{ url(\'customers/meeting/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")->pluck('name', 'id');

        $staffs = $this->userRepository->getStaff()
	            ->pluck('full_name', 'id')->prepend(trans('salesteam.main_staff'),'');

        $privacy = $this->optionRepository->getAll()
            ->where('category', 'privacy')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value');

        $show_times = $this->optionRepository->getAll()
            ->where('category', 'show_times')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value');

        $customers = Customer::all()->pluck('user_id','id');
        $company_customer = [];
        foreach ($customers as $customer){
            $company_customer[]=$customer;
        }
        $company_attendees = User::whereIn('id',$company_customer)->get()->pluck('full_name','id');

        view()->share('privacy', $privacy);
        view()->share('show_times', $show_times);
        view()->share('staffs', $staffs);
        view()->share('companies', $companies);
        view()->share('company_attendees',$company_attendees);
    }

    public function calendar()
    {
        $title = trans('meeting.meetings');
        return view('customers.meeting.calendar', compact('title', 'opportunity'));
    }

    public function calendar_data()
    {
        info('aaa');
        $events = array();
        $meetings = Meeting::where('company_attendees',Sentinel::getUser()->id)->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => $meeting->starting_date,
                    'end_date' => $meeting->ending_date,
                    'type' => 'meeting'
                ];
            });
        foreach ($meetings as $d) {
            $event = [];
            $dateFormat = Settings::get('date_format');
            $timeFormat = Settings::get('time_format');
            $start_date = Carbon::createFromFormat($dateFormat.' '.$timeFormat,$d['start_date'])->format('M d Y');
            $end_date = Carbon::createFromFormat($dateFormat.' '.$timeFormat,$d['end_date'])->addDay()->format('M d Y');
            $event['title'] = $d['title'];
            $event['id'] = $d['id'];
            $event['start'] = $start_date;
            $event['end'] = $end_date;
            $event['allDay'] = true;
            $event['description'] = $d['title'] . '&nbsp;<a href="' . url($d['type'] . '/' . $d['id'] . '/edit') . '" class="btn btn-sm btn-success"><i class="fa fa-pencil-square-o">&nbsp;</i></a>';
            array_push($events, $event);
        }
        return json_encode($events);
    }
}
