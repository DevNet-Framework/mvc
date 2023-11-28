<?php

namespace Application\Controllers;

use DevNet\Web\Endpoint\Controller;
use DevNet\Web\Endpoint\IActionResult;
use DevNet\Web\Endpoint\Route;
use DevNet\Web\Http\HttpException;

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
                $error = new HttpException("Sorry! You don't have permission for this action.", 403);
                break;
            case 404:
                $error = new HttpException("Sorry! The page you are looking for cannot be found.", 404);
                break;
            case 405:
                $error = new HttpException("Sorry! Your request method is not allowed.", 405);
                break;
            default:
                $error = new HttpException("Woops! Looks like something went wrong.", 500);
                break;
        }

        return $this->view(['Error' => $error]);
    }
}
