<?php
// src/Controller/User/GetMe.php
namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Normalizer\User\UserNormalizer;

class GetMe extends UserController
{
    #[Route(
        '/api/me',
        name: 'me',
        methods: ['GET']
    )]
    public function __invoke(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager): Response
    {
        $user = null;

        if($tokenStorageInterface->getToken()){
            $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());
        
            $user = $this->userRepository->findOneBy(array("email" => $decodedJwtToken["username"]));
        }

        if($user){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $user], [new UserNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_UNAUTHORIZED, "Unauthorized");
        }
    }
}