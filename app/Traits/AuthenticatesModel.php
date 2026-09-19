<?php

namespace App\Traits;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;

trait AuthenticatesModel
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
}
