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
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Home:default');
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

        $form->addProtection('Security token expired, please refresh and sign in again.');
        $form->addEmail('username')->setRequired();
        $form->addPassword('password')->setRequired();
        $form->addSubmit('send');

        $form->onSuccess[] = function (Form $form, \stdClass $data): void {
            if ($this->isAjax()) {
                try {
                    // Nette now automatically knows how to handle this!
                    $this->securityUser->login($data->username, $data->password);

                    $this->sendResponse(new JsonResponse([
                        'success' => true,
                        'message' => 'Login successful!'
                    ]));
                } catch (AuthenticationException $e) {
                    $this->getHttpResponse()->setCode(Nette\Http\IResponse::S401_UNAUTHORIZED);
                    $this->sendResponse(new JsonResponse([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Invalid email address or password.'
                    ]));
                }
            }
        };

        return $form;
    }
}