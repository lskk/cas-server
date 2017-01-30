<?php

namespace App\Http\Controllers\Cas;


use App\Events\CasUserLoginEvent;
use App\Exceptions\CAS\CasException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use App\Services\Service;
use App\Http\Controllers\Controller;
use App\Services\Ticket;
use App\User;

class SecurityController extends Controller
{
    use AuthenticatesUsers, ThrottlesLogins;

    protected $username = 'name';

    public function loginPageAction(Request $request)
    {
        $service = $request->get('service', '');
        $errors  = [];
        if (!empty($service)) {
            //service not found in white list
            if (!Service::isUrlValid($service)) {
                $errors[] = (new CasException(CasException::INVALID_SERVICE))->getCasMsg();
            }
        }

        $user = \Auth::user();
        //user already has sso session
        if ($user) {
            //must not be transparent
            if ($request->get('warn') === 'true' && !empty($service)) {
                $query = $request->query->all();
                unset($query['warn']);
                $url = route('cas_login_action', $query);

                return view('auth.login_warn', ['url' => $url, 'service' => $service]);
            }

            return $this->authenticated($request, $user);
        }

        $view = view('auth.login', ['origin_req' => $request->query->all()]);
        if (!empty($errors)) {
            $view->withErrors(['global' => $errors]);
        }

        return $view;
    }

    protected function authenticated(Request $request, User $user)
    {
        return event(new CasUserLoginEvent($request, $user), [], true);
    }
}
