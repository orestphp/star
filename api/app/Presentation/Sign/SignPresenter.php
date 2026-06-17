<?php
// [app/Presentation/Sign/SignPresenter.php]

declare(strict_types=1);

namespace App\Presentation\Sign;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\Responses\JsonResponse;
use Nette\Security\AuthenticationException;

final class SignPresenter extends Nette\Application\UI\Presenter
{
    /** @inject */
    public Nette\Security\User $securityUser;

    public function actionIn(): void
    {
        // 1. If already logged in, send them away
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
        }

        // 2. Capture direct AJAX POST requests bypassing the form pipeline
        if ($this->getHttpRequest()->isMethod('POST')) {
            try {
                $this->securityUser->logout(true);

                // Read values directly from raw post data
                $username = $this->getHttpRequest()->getPost('username') ?: '';
                $password = $this->getHttpRequest()->getPost('password') ?: '';

                if (empty($username) || empty($password)) {
                    throw new AuthenticationException('Email and password fields are required.');
                }

                // Authenticate credentials
                $this->securityUser->login($username, $password);

                $this->sendJson([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $this->link('Home:default')
                ]);

            } catch (AuthenticationException $e) {
                $this->securityUser->logout(true);
                $this->getHttpResponse()->setCode(Nette\Http\IResponse::S401_UNAUTHORIZED);

                $this->sendJson([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Invalid email address or password.'
                ]);
            }
        }
    }

    public function actionOut(): void
    {
        $this->getUser()->logout(true); // Clear identity data permanently from storage
        $this->flashMessage('You have been successfully logged out.', 'info');
        $this->redirect('Sign:in');
    }

    protected function createComponentLoginForm(): Form
    {
        $form = new Form;

        // Use standard protection
        $form->addProtection('Security token expired, please refresh and sign in again.');
        $form->addEmail('username')->setRequired();
        $form->addPassword('password')->setRequired();
        $form->addSubmit('send');

        $form->onSuccess[] = function (Form $form, \stdClass $data): void {
            if ($this->isAjax()) {
                try {
                    // 1. Force wipe any weird session legacy tokens
                    $this->securityUser->logout(true);

                    // 2. Perform credential authentication
                    $this->securityUser->login($data->username, $data->password);

                    // 3. Success! Send response back
                    $this->sendJson([
                        'success' => true,
                        'message' => 'Login successful!',
                        'redirect' => $this->link('Home:default')
                    ]);

                } catch (AuthenticationException $e) {
                    $form->getComponent(Form::PROTECTOR_ID)->loadHttpData();

                    $this->securityUser->logout(true);
                    $this->getHttpResponse()->setCode(Nette\Http\IResponse::S401_UNAUTHORIZED);

                    $this->sendJson([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Invalid credentials.'
                    ]);
                }
            }
        };

        return $form;
    }
}