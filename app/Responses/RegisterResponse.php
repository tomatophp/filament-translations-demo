<?php

namespace App\Responses;


class RegisterResponse extends \TomatoPHP\FilamentAccounts\Responses\RegisterResponse
{
    public function toResponse($request)
    {
        return redirect()->to(url('otp'));
    }
}
