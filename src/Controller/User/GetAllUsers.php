<?php
// src/Controller/User/GetAllUsers.php
namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Normalizer\User\UserNormalizer;

class GetAllUsers extends UserController
{
    #[Route(
        '/api/users',
        name: 'users',
        methods: ['GET']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(): Response
    {
        $users = $this->userRepository->findAll();
        
        if($users){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["total" => count($users), "items" => $users], [new UserNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_OK);
        }
    }
}