<?php
// [app/Middleware/CsrfMiddlewareListener.php]

declare(strict_types=1);

namespace App\Middleware;

use Nette;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;

class CsrfMiddlewareListener
{
    public function __construct(
        private Nette\Http\IRequest $httpRequest,
        private Nette\Http\IResponse $httpResponse,
        private Nette\Http\Session $session
    ) {}

    public function __invoke(Application $application, Presenter $presenter): void
    {
        // Intercept all POST methods framework-wide
        if ($this->httpRequest->isMethod('POST')) {

            // Allow skipping specific presenters if needed
            if ($presenter instanceof \App\Presentation\Sign\SignPresenter) {
                return;
            }

            $passedToken = $this->httpRequest->getPost('_sec') ?: $presenter->getParameter('_sec');
            $expectedToken = $this->session->getSection('Nette.Forms.Form')->token;

            if (!$passedToken || !$expectedToken || !hash_equals($expectedToken, $passedToken)) {
                $this->httpResponse->setCode(Nette\Http\IResponse::S403_FORBIDDEN);
                $presenter->sendJson(['success' => false, 'error' => 'Global CSRF validation failed.']);
            }
        }
    }
}