<?php

namespace Application\Models;

class Registration
{
    public string $Name;
    public string $Email;
    public string $Password;

    public function isValid(): bool
    {
        if (empty($this->Name) || empty($this->Email) || empty($this->Password)) {
            return false;
        }

        return true;
    }
}
