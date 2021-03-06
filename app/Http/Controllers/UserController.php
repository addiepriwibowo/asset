<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdate;
use App\Service\DataMessage;
use App\Service\DatatableParameters;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\UserStore;
use App\Models\User;
use App\Service\User as UserService;
use App\Service\DatatableGenerator;

class UserController extends Controller
{
    use DataMessage;

    protected $userService;

    /**
     * UserController constructor.
     * @param $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return view('users.list');
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData()
    {
        return $this->userService->datatableData();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['sites'] = $this->userService->site()->all();
        $data['roles'] = $this->userService->role()->all();
        return view('users.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserStore $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStore $request)
    {
        $this->userService->store($request->except('_token'));

        return redirect('user')->with($this->getMessage('store'));
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['user'] = User::find($id);
        $data['sites'] = $this->userService->site()->all();
        $data['roles'] = $this->userService->role()->all();
        $data['selectedSites'] = $this->userService->selectedSites($id);
        $data['selectedRoles'] = $this->userService->selectedRoles($id);

        return view('users.edit', $data);
    }

    /**
     * @param UserUpdate $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserUpdate $request, $id)
    {
        $this->userService->update($id, $request->only(['name', 'email', 'sites', 'roles']));

        return redirect('user')->with($this->getMessage('update'));
    }

    public function destroy($id)
    {
        $this->userService->destroy($id);

        return redirect('user')->with($this->getMessage('delete'));
    }

    public function profile($id)
    {
        if (! $this->userService->isCurrentUser($id)) {
            return redirect('dashboard')->withErrors(['There is a problem with your account.']);
        }
        $user = $this->userService->getUserById($id);
        $data['user'] = $user;

        return view('profiles.index', $data);
    }

    public function editProfile($id)
    {
        if (! $this->userService->isCurrentUser($id)) {
            return redirect('dashboard')->withErrors(['There is a problem with your account.']);
        }
        $user = $this->userService->getUserById($id);
//        var_dump($user->userDetail); exit;
        $data['user'] = $user;

        return view('profiles.edit', $data);
    }

    public function updateProfile(Request $request, $id)
    {
        if (! $this->userService->isCurrentUser($id)) {
            return redirect('dashboard')->withErrors(['There is a problem with your account.']);
        }

        $this->userService->updateProfile($id, $request->except(['_token']));

        return redirect('user-profile/' . $id)->with($this->getMessage('update'));
    }

    public function updateAvatar(Request $request, $id)
    {
        if (! $this->userService->isCurrentUser($id)) {
            return redirect('dashboard')->withErrors(['There is a problem with your account.']);
        }

        if ($this->userService->updateAvatar($id, $request)) {
            return redirect('user-profile/' . $id)->with($this->getMessage('update'));
        }

        return redirect('user-profile/' . $id . '/edit#change_avatar')
            ->with(['message' => 'There are some problems with your data.'])
            ->withInput();
    }

    /**
     * @param Request $request
     * @param $id
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request, $id)
    {
        if (! $this->userService->isCurrentUser($id)) {
            return redirect('dashboard')->withErrors(['There is a problem with your account.']);
        }

        if ($this->userService->updatePassword($id, $request->except(['_token'])) ) {
            return redirect('user-profile/' . $id)->with($this->getMessage('update'));
        }

        return redirect('user-profile/' . $id . '/edit#change_password')
            ->with(['message' => 'There are some problems with your data.'])
            ->withInput();

    }

}
