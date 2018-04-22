<?php

namespace App\Http\Controllers\Users;

use App\Models\Task;
use App\Http\Requests\TaskRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Sentinel;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;

class TaskController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TaskController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;

        view()->share('type', 'task');
    }
    public function index()
    {
        $title = trans('task.tasks');
        $users = $this->userRepository->getAllNew()->get()
            ->filter(function ($user) {
                return ($user->inRole('staff') || $user->inRole('admin'));
            })
            ->map(function ($user) {
                return [
                    'name' => $user->full_name .' ( '.$user->email.' )' ,
                    'id' => $user->id
                ];
            })
            ->pluck('name', 'id')->prepend(trans('task.user'),'');

        return view('user.task.index', compact('title','users'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(TaskRequest $request)
    {
        $task = new Task($request->except('_token','full_name'));
        $task->save();
        return $task->id;
    }

    /**
     * @param Driver $driver
     * @param DriverRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Task $task, Request $request)
    {
        $task->update($request->except('_method', '_token'));
    }
    /**
     * Delete the given Driver.
     *
     * @param  int $id
     * @return Redirect
     */
    public function delete(Task $task)
    {
        $task->delete();

    }

    /**
     * Ajax Data
     */
    public function data()
    {
        return Task::where('user_id', $this->user->id)
            ->orderBy("finished", "ASC")
            ->orderBy("task_deadline", "DESC")
            ->get()
            ->map(function ($task) {
                return [
                    'task_from' => $task->task_from_users->full_name,
                    'id' => $task->id,
                    'finished' => $task->finished,
                    'task_deadline' => $task->task_deadline,
                    "task_description" => $task->task_description,
                ];
            });

    }
}