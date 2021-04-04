<?php

namespace Application\Controllers;

use DevNet\Web\Mvc\Controller;
use DevNet\Web\Mvc\IActionResult;

class HomeController extends Controller
{
    public function index() : IActionResult
    {
        return $this->view();
    }
}
