<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentaireController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/video/{id}/comments', name: 'video_comments_list')]
    public function listComments(Video $video): Response
    {
        $comments = $video->getCommentaires()->toArray();
        
        // Trier les commentaires par date de publication décroissante
        usort($comments, function($a, $b) {
            return $b->getDatePublication() <=> $a->getDatePublication();
        });
        
        return $this->render('all_video/_comments.html.twig', [
            'comments' => $comments,
            'video' => $video
        ]);
    }

    #[Route('/video/{id}/comment', name: 'video_comment_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addComment(Request $request, Video $video): Response
    {
        // Récupérer le contenu du commentaire
        $content = $request->request->get('content');
        
        // Validation du contenu
        if (empty(trim($content))) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('allficheVideo', ['id' => $video->getId()]);
        }

        // Créer le nouveau commentaire
        $comment = new Commentaire();
        $comment->setContenu($content);
        $comment->setDatePublication(new \DateTime());
        $comment->addPosteCom($this->getUser());
        $comment->setVideo($video);
        $comment->setLikesCount(0);
        $comment->setDislikesCount(0);

        // Persister le commentaire
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté.');
        return $this->redirectToRoute('allficheVideo', ['id' => $video->getId()]);
    }

    #[Route('/commentaire/{id}/like', name: 'commentaire_like', methods: ['POST'])]
    public function like(Commentaire $commentaire): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour liker un commentaire'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $commentaire->addLikeCom($user);
        $this->entityManager->persist($commentaire);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'likes' => $commentaire->getLikesCount(),
            'dislikes' => $commentaire->getDislikesCount(),
            'isLiked' => $commentaire->isLikedByUser($user),
            'isDisliked' => $commentaire->isDislikedByUser($user)
        ]);
    }

    #[Route('/commentaire/{id}/dislike', name: 'commentaire_dislike', methods: ['POST'])]
    public function dislike(Commentaire $commentaire): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour disliker un commentaire'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $commentaire->addDislikeCom($user);
        $this->entityManager->persist($commentaire);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'likes' => $commentaire->getLikesCount(),
            'dislikes' => $commentaire->getDislikesCount(),
            'isLiked' => $commentaire->isLikedByUser($user),
            'isDisliked' => $commentaire->isDislikedByUser($user)
        ]);
    }
}
