<?php
// src/Controller/Creator/GroupCreatorShow.php
namespace App\Controller\Creator;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Normalizer\Creator\CreatorNormalizer;
use App\Repository\ShowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

class GroupCreatorShow extends CreatorController
{
    #[Route(
        '/api/creators/{creatorId}/shows/{showId}',
        name: 'group_creator_show',
        methods: ['POST'],
        requirements: ['creatorId' => '\d+', 'showId' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function groupCreatorToShow(int $creatorId, int $showId, ShowRepository $showRepository, EntityManagerInterface $entityManager): Response
    {
        $creator = $this->creatorRepository->find($creatorId);
        if(!$creator){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Creator not found");
        }

        $show = $showRepository->find($showId);
        if(!$show){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $shows = $creator->getShows();
        if($shows->contains($show)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_CONFLICT, "This Creator is already linked to this Show");
        }
        else{
            $creator->addShow($show);
            $entityManager->persist($creator);
            $entityManager->flush();

            return $this->responseHandler->createResponse(Response::HTTP_OK, ["items" => $creator], [new CreatorNormalizer]);
        }
    }

    #[Route(
        '/api/creators/{creatorId}/shows/{showId}',
        name: 'ungroup_creator_show',
        methods: ['DELETE'],
        requirements: ['creatorId' => '\d+', 'showId' => '\d+']
    )]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_USER') and is_granted('ROLE_ADMIN')"), statusCode: 403, message: 'Forbidden')]
    public function removeCreatorFromShow(int $creatorId, int $showId, ShowRepository $showRepository, EntityManagerInterface $entityManager): Response
    {
        $creator = $this->creatorRepository->find($creatorId);
        if(!$creator){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Creator not found");
        }

        $show = $showRepository->find($showId);
        if(!$show){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Show not found");
        }

        $shows = $creator->getShows();
        if(!$shows->contains($show)){
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "No link found between this Creator and this Show");
        }
        else{
            $creator->removeShow($show);
            $entityManager->persist($creator);
            $entityManager->flush();
            
            return $this->responseHandler->createResponse(Response::HTTP_NO_CONTENT);
        }
    }
}