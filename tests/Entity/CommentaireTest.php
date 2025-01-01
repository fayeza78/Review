<?php

namespace App\Tests\Entity;

use App\Entity\Commentaire;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CommentaireTest extends TestCase
{
    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        return $user;
    }

    public function testInitialLikeDislikeCounts()
    {
        $commentaire = new Commentaire();
        
        $this->assertEquals(0, $commentaire->getLikesCount(), 'Le compteur de likes doit être initialisé à 0');
        $this->assertEquals(0, $commentaire->getDislikesCount(), 'Le compteur de dislikes doit être initialisé à 0');
    }

    public function testSingleUserCanOnlyLikeOrDislikeOnce()
    {
        $commentaire = new Commentaire();
        $user = $this->createUser('test@example.com');

        // Ajouter un like
        $commentaire->addLikeCom($user);
        $this->assertEquals(1, $commentaire->getLikesCount(), 'Le compteur de likes doit être à 1');
        $this->assertTrue($commentaire->isLikedByUser($user), 'L\'utilisateur doit avoir liké le commentaire');

        // Tenter de disliker (ne doit rien faire)
        $commentaire->addDislikeCom($user);
        $this->assertEquals(1, $commentaire->getLikesCount(), 'Le compteur de likes doit rester à 1');
        $this->assertEquals(0, $commentaire->getDislikesCount(), 'Le compteur de dislikes doit rester à 0');
        $this->assertTrue($commentaire->isLikedByUser($user), 'L\'utilisateur doit toujours avoir liké le commentaire');

        // Réinitialiser
        $commentaire = new Commentaire();

        // Ajouter un dislike
        $commentaire->addDislikeCom($user);
        $this->assertEquals(1, $commentaire->getDislikesCount(), 'Le compteur de dislikes doit être à 1');
        $this->assertTrue($commentaire->isDislikedByUser($user), 'L\'utilisateur doit avoir disliké le commentaire');

        // Tenter de liker (ne doit rien faire)
        $commentaire->addLikeCom($user);
        $this->assertEquals(1, $commentaire->getDislikesCount(), 'Le compteur de dislikes doit rester à 1');
        $this->assertEquals(0, $commentaire->getLikesCount(), 'Le compteur de likes doit rester à 0');
        $this->assertTrue($commentaire->isDislikedByUser($user), 'L\'utilisateur doit toujours avoir disliké le commentaire');
    }

    public function testMultipleUsersCanLikeOrDislike()
    {
        $commentaire = new Commentaire();
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        // Deux utilisateurs différents peuvent liker/disliker
        $commentaire->addLikeCom($user1);
        $commentaire->addDislikeCom($user2);

        $this->assertEquals(1, $commentaire->getLikesCount(), 'Le compteur de likes doit être à 1');
        $this->assertEquals(1, $commentaire->getDislikesCount(), 'Le compteur de dislikes doit être à 1');
        $this->assertTrue($commentaire->isLikedByUser($user1), 'User1 doit avoir liké le commentaire');
        $this->assertTrue($commentaire->isDislikedByUser($user2), 'User2 doit avoir disliké le commentaire');
    }
}