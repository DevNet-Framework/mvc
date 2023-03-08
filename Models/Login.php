<?php

namespace Application\Models;

class Login
{
    public string $Username;
    public string $Password;
    public bool $Remember = false;

    public function isValide(): bool
    {
        if (empty($this->Username) || empty($this->Password)) {
            return false;
        }

        return true;
    }
}
