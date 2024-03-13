<?php

namespace Application\Controllers;

use Application\Models\Login;
use Application\Models\Registration;
use Application\Models\User;
use DevNet\Core\Endpoint\Controller;
use DevNet\Core\Endpoint\IActionResult;
use DevNet\Core\Endpoint\Filters\Authorize;
use DevNet\Core\Endpoint\Filters\ValidateAntiForgery;
use DevNet\Core\Endpoint\Route;
use DevNet\Http\Message\HttpContext;
use DevNet\Security\Claims\ClaimsIdentity;
use DevNet\Security\Claims\Claim;
use DevNet\Security\Authentication\AuthenticationScheme;
use DevNet\Security\Authentication\IAuthentication;
use DevNet\System\Collections\ArrayList;
use DevNet\System\Linq;

class AccountController extends Controller
{
    private HttpContext $httpContext;
    private IAuthentication $authentication;

    public function __construct(HttpContext $httpContext, IAuthentication $authentication)
    {
        $this->httpContext = $httpContext;
        $this->authentication = $authentication;
    }

    #[Authorize(roles: ['Admin', 'User'])]
    #[Route(path: '/account', method: 'GET')]
    public function index(): IActionResult
    {
        $user  = $this->httpContext->User;
        $claim = $user->findClaim(fn ($claim) => $claim->Type == 'Name');
        $name  = $claim ? $claim->Value : null;
        return $this->view(['Name' => $name]);
    }

    #[Route(path: '/register', method: 'GET')]
    public function register(): IActionResult
    {
        return $this->view();
    }

    #[ValidateAntiForgery]
    #[Route(path: '/account/create', method: 'POST')]
    public function create(Registration $form): IActionResult
    {
        if (!$form->isValid()) {
            return $this->view(['success' => false]);
        }

        $data = [];
        if (is_file(dirname(__DIR__, 2) . '/data.json')) {
            $json = file_get_contents(dirname(__DIR__, 2) . '/data.json');
            $data = json_decode($json);
        }

        $user = new User();
        $user->Name = $form->Name;
        $user->Username = $form->Email;
        $user->Password = password_hash($form->Password, PASSWORD_DEFAULT);
        $user->Role     = "User";

        $data[] = $user;
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(dirname(__DIR__, 2) . '/data.json', $json);

        return $this->view(['success' => true]);
    }

    #[Route(path: '/login', method: 'GET')]
    public function login(): IActionResult
    {
        return $this->view();
    }

    #[ValidateAntiForgery]
    #[Route(path: '/account/authenticate', method: 'POST')]
    public function authenticate(Login $form): IActionResult
    {
        if ($this->httpContext->User->isAuthenticated()) {
            return  $this->redirect('/account');
        }

        if (!$form->isValid()) {
            return  $this->redirect('/login');
        }

        if (!is_file(dirname(__DIR__, 2) . '/data.json')) {
            return  $this->redirect('/login');
        }

        $json  = file_get_contents(dirname(__DIR__, 2) . '/data.json');
        $data  = json_decode($json);
        $users = new ArrayList('object');
        $users->addRange($data);

        $user = $users->where(fn ($user) => $user->Username == $form->Username)->first();
        if (!$user || !password_verify($form->Password, $user->Password)) {
            return  $this->redirect('/login');
        }

        $identity = new ClaimsIdentity(AuthenticationScheme::CookieSession);
        $identity->addClaim(new Claim('Name', $user->Name));
        $identity->addClaim(new Claim('Username', $user->Username));
        $identity->addClaim(new Claim('Role', $user->Role));

        $this->authentication->signIn($identity, $form->Remember);

        return $this->redirect('/account');
    }

    #[Route(path: '/logout', method: 'GET')]
    public function logout(): IActionResult
    {
        $this->authentication->signOut();
        return $this->redirect('/login');
    }
}
