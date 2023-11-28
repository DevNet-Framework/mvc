<?php

namespace Application\Controllers;

use DevNet\System\Linq;
use DevNet\System\Collections\ArrayList;
use DevNet\Web\Endpoint\Controller;
use DevNet\Web\Endpoint\IActionResult;
use DevNet\Web\Endpoint\Route;
use DevNet\Web\Security\Claims\ClaimsIdentity;
use DevNet\Web\Security\Claims\Claim;
use DevNet\Web\Security\Authentication\AuthenticationScheme;
use DevNet\Web\Security\Authentication\IAuthentication;
use DevNet\Web\Security\Authorization\Authorize;
use DevNet\Web\Security\Tokens\Csrf\Validate;
use DevNet\Web\Http\HttpContext;
use Application\Models\Login;
use Application\Models\Registration;
use Application\Models\User;

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

    #[Validate]
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

    #[Validate]
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
