<?php

namespace Application\Models;

class Login
{
    public string $Username;
    public string $Password;
    public bool $Remember = false;

    public function isValide(): bool
    {
        if (empty($this->username) || empty($this->password)) {
            return false;
        }

        return true;
    }
}
