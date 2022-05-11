<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clase abstracta API
 * Guía: https://github.com/Cap-Coding/symfony_api/
 */
abstract class AbstractApiController extends AbstractFOSRestController
{
    protected function respond($data, int $statusCode = Response::HTTP_OK): Response
    {
        return $this->handleView($this->view($data, $statusCode));
    }
}
