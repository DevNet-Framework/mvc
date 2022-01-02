<?php

namespace Application\Controllers;

use DevNet\Web\Mvc\Controller;
use DevNet\Web\Mvc\IActionResult;

class HomeController extends Controller
{
    public function index(): IActionResult
    {
        return $this->view();
    }

    public function about(): IActionResult
    {
        return $this->view();
    }

    public function error(): IActionResult
    {
        $error = new \Exception("Sorry! Looks like this page doesn't exist.", 404);
        if ($this->HttpContext->Error) {
            switch ($this->HttpContext->Error->getCode()) {
                case 404:
                    break;
                case 403:
                    $error = new \Exception("Sorry! Your request is not allowed.", 403);
                    break;
                default:
                    $error = new \Exception("Woops! Looks like something went wrong.", 500);
                    break;
            }
        }

        return $this->view($error);
    }
}
