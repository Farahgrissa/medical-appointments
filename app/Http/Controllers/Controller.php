<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    // Fournit $this->authorize() pour utiliser les Policies
    use AuthorizesRequests;
}
