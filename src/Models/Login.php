<?php

namespace Application\Models;

class Login
{
    public string $Username;
    public string $Password;
    public bool $Remember = false;

    public function isValid(): bool
    {
        if (empty($this->Username) || empty($this->Password)) {
            return false;
        }

        return true;
    }
}
