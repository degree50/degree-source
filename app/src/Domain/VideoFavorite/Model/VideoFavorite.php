<?php

namespace App\Domain\VideoFavorite\Model;

use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\User\Model\User;
use App\Domain\Video\Model\Video;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class VideoFavorite
{
    use IdentityTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "favoriteVideos")]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Video $video;

    public function __construct(User $user, Video $video, string $id = null)
    {
        $this->generateOrSetId($id);
        $this->user = $user;
        $this->video = $video;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }
}
