<?php
// src/Controller/User/GetUser.php
namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Normalizer\User\UserNormalizer;

class GetUser extends UserController
{
    #[Route(
        '/api/users/{id}',
        name: 'user',
        methods: ['GET']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id): Response
    {
        $user = $this->userRepository->findOneBy(array("id" => $id));

        if($user){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $user], [new UserNormalizer]);
        }
        else{
            return $this->responseHandler->createResponse(Response::HTTP_NOT_FOUND, "User not found");
        }
    }
}