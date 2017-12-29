<?php

namespace Solunes\Pagostt\App\Controllers\Integrations;

use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;

use App\Http\Requests;

class BaseController extends Controller {

    use Helpers;
    
    public function __construct(){
        //$this->middleware('jwt.auth', ['except' => ['register-attendance', 'register-operator']]);
        //$this->middleware('api.throttle');
        // Only apply to a subset of methods.
        //$this->middleware('api.auth', ['only' => ['index']]);
    }

}