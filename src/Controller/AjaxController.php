<?php

namespace Pimcorecasts\Bundle\HashCash\Controller;

use Pimcore\Cache;
use Pimcore\Controller\FrontendController;
use Pimcorecasts\Bundle\HashCash\Service\HashCashService;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends FrontendController
{
    #[Route(path: '/pchc/ajax/create-stamp', name: 'pchc_create-stamp')]
    public function createStamp(HashCashService $hashCashService) {
        Cache::disable();
        return $this->json($hashCashService->createStamp(), 200, [
            'cache-control' => 'no-cache, no-store, must-revalidate',
            'pragma' => 'no-cache',
            'expires' => 0
        ]);
    }
}
