<?php

namespace Application\Models;

class Registration
{
    public string $Name;
    public string $Email;
    public string $Password;

    public function isValide(): bool
    {
        if (empty($this->name) || empty($this->Email) || empty($this->password)) {
            return false;
        }

        return true;
    }
}
