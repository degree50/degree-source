<?php

namespace App\Domain\CourseRole\Model;

use App\Domain\Course\Model\Course;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\User\Model\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class CourseRole
{
    use IdentityTrait;

    const string DOZENT = 'DOZENT';
    const string STUDENT = 'STUDENT';
    const array ROLES = [self::DOZENT, self::STUDENT];

    #[Assert\NotBlank]
    #[Assert\Choice(choices: CourseRole::ROLES)]
    #[ORM\Column]
    public string $name = '';

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ["persist"], inversedBy: "courseRoles")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Course::class, cascade: ["persist"], inversedBy: "courseRoles")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Course $course;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isCourseDozent(): bool
    {
        return $this->name === self::DOZENT;
    }

    public function isCourseStudent(): bool
    {
        return $this->name === self::STUDENT;
    }

    public function __toString()
    {
        return sprintf('CourseRole<%s, %s, %s>', $this->getCourse()->getName(), $this->getUser()->getEmail(), $this->name);
    }
}
