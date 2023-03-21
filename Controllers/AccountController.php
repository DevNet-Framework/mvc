<?php

namespace Application\Controllers;

use DevNet\System\Linq;
use DevNet\System\Collections\ArrayList;
use DevNet\Web\Action\Filters\Antiforgery;
use DevNet\Web\Action\Filters\Authorize;
use DevNet\Web\Action\IActionResult;
use DevNet\Web\Controller\AbstractController;
use DevNet\Web\Security\Claims\ClaimsIdentity;
use DevNet\Web\Security\Claims\ClaimType;
use DevNet\Web\Security\Claims\Claim;
use Application\Models\Login;
use Application\Models\Registration;
use Application\Models\User;

/**
 * This is an example on how to create registration and login system using claims without SQL database.
 * This example dosen't encrypt the user password or data, so it's not recommanded for production,
 * Use DevNet Identity Manager instead, or encrypt you own data.
 */
#[Authorize(roles: ['admin', 'member'])]
class AccountController extends AbstractController
{
    public function index(): IActionResult
    {
        $user = $this->HttpContext->User;
        $claim = $user->findClaim(fn ($claim) => $claim->Type == ClaimType::Name);
        $name = $claim ? $claim->Value : null;
        $this->ViewData['Name'] = $name;
        return $this->view();
    }

    #[Authorize]
    #[Antiforgery]
    public function login(Login $form): IActionResult
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

        $users = new ArrayList('object');
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
        $identity->addClaim(new Claim(ClaimType::Role, 'member'));
        $authentication = $this->HttpContext->Authentication;
        $authentication->signIn($identity, $form->Remember);

        return $this->redirect('/account/index');
    }

    #[Authorize]
    #[AntiForgery]
    public function register(Registration $form): IActionResult
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
        $authentication->signOut();
        return $this->redirect('/account/login');
    }
}
