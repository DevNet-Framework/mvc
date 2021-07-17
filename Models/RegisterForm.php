<?php

namespace Application\Models;

class RegisterForm
{
    public string $Name;
    public string $Email;
    public string $Password;

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function __set(string $name, $value)
    {
        $this->$name = $value;
    }

    public function isValide(): bool
    {
        if (empty($this->Name) || empty($this->Email) || empty($this->Password)) {
            return false;
        }

        return true;
    }
}
