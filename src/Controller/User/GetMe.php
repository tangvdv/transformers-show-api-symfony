<?php
// src/Controller/User/GetMe.php
namespace App\Controller\User;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Normalizer\User\UserNormalizer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GetMe extends UserController
{
    #[Route(
        '/api/me',
        name: 'me',
        methods: ['GET']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager): Response
    {
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());

        $user = $this->userRepository->findOneBy(array("email" => $decodedJwtToken["username"]));

        if($user){
            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $user], [new UserNormalizer]);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_UNAUTHORIZED, "Unauthorized");
        }
    }
}