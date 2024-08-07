<?php

namespace App\Domain\Fachbereich\Model;

use App\Domain\EntityTraits\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Fachbereich
{
    use IdentityTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    private string $name = '';

    public function __construct(?string $id = null)
    {
        $this->generateOrSetId($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
