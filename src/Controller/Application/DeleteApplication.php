<?php
// src/Controller/Application/DeleteApplication.php
namespace App\Controller\Application;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\UserRepository;

class DeleteApplication extends ApplicationController
{
    #[Route(
        '/api/applications/{id}',
        name: 'delete_application',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER')"), statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository): Response
    {
        $decodedJwtToken = $jwtManager->decode($tokenStorageInterface->getToken());

        $user = $userRepository->findOneBy(array("email" => $decodedJwtToken["username"]));
        if(!$user){
            return $this->responseHandler->createErrorResponse(Response::HTTP_FORBIDDEN, "Forbidden");
        }

        $application = $this->applicationRepository->findOneBy(array("id" => $id, "user" => $user));

        if($application){
            foreach($application->getStats() as $stat){
                $entityManager->remove($stat);
            }

            $entityManager->remove($application);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Application not found");
        }
    }
}