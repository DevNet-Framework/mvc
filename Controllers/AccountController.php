<?php

namespace Application\Controllers;

use DevNet\System\Linq;
use DevNet\System\Type;
use DevNet\System\Collections\ArrayList;
use DevNet\Web\Mvc\Controller;
use DevNet\Web\Mvc\IActionResult;
use DevNet\Web\Mvc\Filters\AuthorizeFilter;
use DevNet\Web\Mvc\Filters\AntiForgeryFilter;
use DevNet\Web\Security\ClaimsPrincipal;
use DevNet\Web\Security\ClaimsIdentity;
use DevNet\Web\Security\ClaimType;
use DevNet\Web\Security\Claim;
use Application\Models\LoginForm;
use Application\Models\RegisterForm;
use Application\Models\User;

/**
 * This is an example on how to create registration and login system using claims without SQL database.
 * This example dosen't encrypt your data, so it's not recommanded for production,
 * Use DevNet Identity Manager instead, or encrypt you own data.
 */
class AccountController extends Controller
{
    public function __construct()
    {
        $this->filter('index', AuthorizeFilter::class);
        $this->filter('login', AntiForgeryFilter::class);
        $this->filter('register', AntiForgeryFilter::class);
    }

    public function index(): IActionResult
    {
        $user = $this->HttpContext->User;
        $claim = $user->findClaim(fn ($claim) => $claim->Type == ClaimType::Name);
        $name = $claim ? $claim->Value : null;
        $this->ViewData['Name'] = $name;
        return $this->view();
    }

    public function login(LoginForm $form): IActionResult
    {
        $user = $this->HttpContext->User;

        if ($user->isAuthenticated()) {
            return $this->redirect('/account/index');
        }

        if (!$form->isValide()) {
            return $this->view();
        }

        if (!file_exists(__DIR__ . '/../data.json')) {
            return $this->view();
        }

        $json = file_get_contents(__DIR__ . '/../data.json');
        $data = json_decode($json);

        $users = new ArrayList(Type::Object);
        $users->addrange($data);

        $user = $users->where(fn ($user) => $user->Username == $form->Username)->first();

        if (!$user) {
            return $this->view();
        }

        if ($user->Password != $form->Password) {
            return $this->view();
        }

        $identity = new ClaimsIdentity('AuthenticationUser');
        $identity->addClaim(new Claim(ClaimType::Name, $user->Name));
        $identity->addClaim(new Claim(ClaimType::Email, $user->Username));
        $identity->addClaim(new Claim(ClaimType::Role, 'Memeber'));
        $userPrincipal  = new ClaimsPrincipal($identity);
        $authentication = $this->HttpContext->Authentication;
        $authentication->SignIn($userPrincipal, $form->Remember);

        return $this->redirect('/account/index');
    }

    public function register(RegisterForm $form): IActionResult
    {
        $this->ViewData['success'] = false;
        if (!$form->isValide()) {
            return $this->view();
        }

        $data = [];
        if (file_exists(__DIR__ . '/../data.json')) {
            $json = file_get_contents(__DIR__ . '/../data.json');
            $data = json_decode($json, true);
        }

        $user = new User();
        $user->Name = $form->Name;
        $user->Username = $form->Email;
        $user->Password = $form->Password;

        $data[] = $user;
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(__DIR__ . '/../data.json', $json);

        $this->ViewData['success'] = true;
        return $this->view();
    }

    public function logout(): IActionResult
    {
        $authentication = $this->HttpContext->Authentication;
        $authentication->SignOut();
        return $this->redirect('/account/login');
    }
}
