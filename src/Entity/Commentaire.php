<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $contenu;

    #[ORM\Column(type: 'datetime')]
    private $datePublication;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'commentaires')]
    private $posteCom;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'likecommentaires')]
    private $likeCom;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'dislikeCommentaires')]
    private $dislikeCom;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $likesCount = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $dislikesCount = 0;

    #[ORM\ManyToOne(targetEntity: Video::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private $video;

    public function __construct()
    {
        $this->posteCom = new ArrayCollection();
        $this->likeCom = new ArrayCollection();
        $this->dislikeCom = new ArrayCollection();
        $this->datePublication = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): self
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPosteCom(): Collection
    {
        return $this->posteCom;
    }

    public function addPosteCom(User $posteCom): self
    {
        if (!$this->posteCom->contains($posteCom)) {
            $this->posteCom[] = $posteCom;
        }

        return $this;
    }

    public function removePosteCom(User $posteCom): self
    {
        $this->posteCom->removeElement($posteCom);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLikeCom(): Collection
    {
        return $this->likeCom;
    }

    /**
     * Ajoute un like et retire le dislike si présent
     */
    public function addLikeCom(User $user): self
    {
        // Si l'utilisateur a déjà liké ou disliké, on ne fait rien
        if ($this->likeCom->contains($user) || $this->dislikeCom->contains($user)) {
            return $this;
        }

        // Ajouter le like
        $this->likeCom->add($user);
        $user->addLikecommentaire($this);

        return $this;
    }

    public function removeLikeCom(User $user): self
    {
        if ($this->likeCom->removeElement($user)) {
            $user->removeLikecommentaire($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getDislikeCom(): Collection
    {
        return $this->dislikeCom;
    }

    /**
     * Ajoute un dislike et retire le like si présent
     */
    public function addDislikeCom(User $user): self
    {
        // Si l'utilisateur a déjà liké ou disliké, on ne fait rien
        if ($this->likeCom->contains($user) || $this->dislikeCom->contains($user)) {
            return $this;
        }

        // Ajouter le dislike
        $this->dislikeCom->add($user);
        $user->addDislikeCommentaire($this);

        return $this;
    }

    public function removeDislikeCom(User $user): self
    {
        if ($this->dislikeCom->removeElement($user)) {
            $user->removeDislikeCommentaire($this);
        }
        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likeCom->count();
    }

    public function getDislikesCount(): int
    {
        return $this->dislikeCom->count();
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): self
    {
        $this->video = $video;
        return $this;
    }

    /**
     * Vérifie si l'utilisateur a liké ce commentaire
     */
    public function isLikedByUser(User $user): bool
    {
        return $this->likeCom->contains($user);
    }

    /**
     * Vérifie si l'utilisateur a disliké ce commentaire
     */
    public function isDislikedByUser(User $user): bool
    {
        return $this->dislikeCom->contains($user);
    }
}
