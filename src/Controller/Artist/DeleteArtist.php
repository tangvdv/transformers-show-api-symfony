<?php
// src/Controller/Artist/DeleteArtist.php
namespace App\Controller\Artist;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteArtist extends ArtistController
{
    #[Route(
        '/api/artist/{id}',
        name: 'delete_artist',
        methods: ['DELETE'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_ADMIN', statusCode: 403, message: 'Forbidden')]
    public function __invoke(int $id, EntityManagerInterface $entityManager): Response
    {
        $artist = $this->artistRepository->find($id);

        if($artist){
            $entityManager->remove($artist);
            $entityManager->flush();

            return $this->responseHandler->createErrorResponse(Response::HTTP_NO_CONTENT);
        }
        else{
            return $this->responseHandler->createErrorResponse(Response::HTTP_NOT_FOUND, "Artist not found");
        }
    }
}