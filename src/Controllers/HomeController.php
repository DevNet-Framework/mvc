<?php

namespace Application\Controllers;

use DevNet\Core\Endpoint\Controller;
use DevNet\Core\Endpoint\IActionResult;
use DevNet\Core\Endpoint\Route;

class HomeController extends Controller
{
    #[Route('/')]
    public function index(): IActionResult
    {
        return $this->view();
    }

    #[Route('/about')]
    public function about(): IActionResult
    {
        return $this->view();
    }

    #[Route('/error')]
    public function error(): IActionResult
    {
        $error = $this->HttpContext->Items->getValue('ErrorException');
        $code  = $error ? $error->getCode() : 404;

        switch ($code) {
            case 401:
                return $this->redirect('/login');
                break;
            case 403:
                $error = new \Exception("Sorry! You don't have permission for this action.", 403);
                break;
            case 404:
                $error = new \Exception("Sorry! The page you are looking for cannot be found.", 404);
                break;
            case 405:
                $error = new \Exception("Sorry! Your request method is not allowed.", 405);
                break;
            default:
                $error = new \Exception("Woops! Looks like something went wrong.", 500);
                break;
        }

        return $this->view(['Error' => $error]);
    }
}
