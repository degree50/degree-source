<?php

namespace App\Domain\EntityTraits;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

trait IdentityTrait
{
    /**
     * The entity Id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "NONE")]
    #[ORM\Column(type: "guid")]
    private string $id;

    public function __construct(?string $id = null)
    {
        $this->generateOrSetId($id);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    private function generateOrSetId(?string $id = null): void
    {
        $this->id = $id ?: Uuid::uuid4()->toString();
    }
}
