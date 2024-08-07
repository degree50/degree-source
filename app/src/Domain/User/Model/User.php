<?php

namespace App\Domain\User\Model;

use App\Domain\Attachment\Model\Attachment;
use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\EntityTraits\IdentityTrait;
use App\Domain\Exercise\Model\Exercise;
use App\Domain\Material\Model\Material;
use App\Domain\Video\Model\Video;
use App\Domain\VideoFavorite\Model\VideoFavorite;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[UniqueEntity(fields: ["email"])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdentityTrait;

    const string ROLE_USER = 'ROLE_USER';
    const string ROLE_ADMIN = 'ROLE_ADMIN';
    const string ROLE_STUDENT = 'ROLE_STUDENT';
    const string ROLE_DOZENT = 'ROLE_DOZENT';
    const string ROLE_SSO_USER = 'ROLE_SSO_USER';

    const string EXPIRATION_DURATION_STRING = '5 years';
    const string DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const string EXPIRATION_NOTICE_DURATION_STRING = '8 months';
    const string VERIFICATION_TIMEOUT_DURATION_STRING = '5 days';

    const int MIN_PASSWORD_LENGTH = 8;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    /**
     * This is solely used to define a plain unencrypted password inside the admin ui which will be encrypted and written
     * to self::password upon persistence.
     * @see EasyAdminSubscriber
     * @see UserCrudController
     */
    private ?string $plainPassword = '';

    #[ORM\Column(type: "string")]
    private string $password;

    /**
     * @var Collection<CourseRole>
     */
    #[ORM\OneToMany(targetEntity: CourseRole::class, mappedBy: "user", orphanRemoval: true)]
    private Collection $courseRoles;

    /**
     * @var Collection<Exercise>
     */
    #[ORM\OneToMany(targetEntity: Exercise::class, mappedBy: "creator", orphanRemoval: true)]
    private Collection $createdExercises;

    /**
     * @var Collection<Video>
     */
    #[ORM\OneToMany(targetEntity: Video::class, mappedBy: "creator", orphanRemoval: true)]
    private Collection $createdVideos;

    /**
     * @var Collection<VideoFavorite>
     */
    #[ORM\OneToMany(targetEntity: VideoFavorite::class, mappedBy: "user", orphanRemoval: true)]
    private Collection $favoriteVideos;

    /**
     * @var Collection<Attachment>
     */
    #[ORM\OneToMany(targetEntity: Attachment::class, mappedBy: "creator", cascade: ["all"], orphanRemoval: true)]
    private Collection $createdAttachments;

    /**
     * @var Collection<Material>
     */
    #[ORM\OneToMany(targetEntity: Material::class, mappedBy: "owner", orphanRemoval: true)]
    private Collection $materials;

    #[ORM\Column(type: "boolean")]
    private bool $dataPrivacyAccepted = false;

    #[ORM\Column(type: "smallint", options: ["default" => 1])]
    private int $dataPrivacyVersion = 1;

    #[ORM\Column(type: "boolean")]
    private bool $termsOfUseAccepted = false;

    #[ORM\Column(type: "smallint", options: ["default" => 0])]
    private int $termsOfUseVersion = 0;

    #[ORM\Column(name: "created_at", type: "datetimetz_immutable")]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: "expiration_date", type: "datetimetz_immutable")]
    private DateTimeImmutable $expirationDate;

    #[ORM\Column(type: "boolean")]
    private bool $isVerified = false;

    #[ORM\Column(type: "boolean")]
    private bool $expirationNoticeSent = false;

    #[ORM\Column(type: "boolean")]
    private bool $isAnonymized = false;

    public function __construct(?string $id = null)
    {
        $this->courseRoles = new ArrayCollection();
        $this->createdExercises = new ArrayCollection();
        $this->createdVideos = new ArrayCollection();
        $this->favoriteVideos = new ArrayCollection();
        $this->generateOrSetId($id);
        $this->createdAt = new DateTimeImmutable();
        $this->expirationDate = $this->createdAt->add(
            DateInterval::createFromDateString(self::EXPIRATION_DURATION_STRING)
        );
        $this->createdAttachments = new ArrayCollection();
        $this->materials = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return $this->getId();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        if ($this->isAnonymized) {
            return '[Gelöschter Nutzer]';
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        // WHY the array_values:
        // The way doctrine saves these roles inside the database sometimes differs (no idea why.).
        // Sometimes doctrine will save them as array list and sometimes it saves them as associative array.
        // However we expect the structure to always be a list, which is why we make sure here,
        // that we actually get one ;)
        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<CourseRole>
     */
    public function getCourseRoles(): Collection
    {
        return $this->courseRoles;
    }

    public function addCourseRole(CourseRole $courseRole): self
    {
        if (!$this->courseRoles->contains($courseRole)) {
            $this->courseRoles[] = $courseRole;
            $courseRole->setUser($this);
        }

        return $this;
    }

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->setRole(self::ROLE_ADMIN, $isAdmin);
    }

    public function setIsStudent(bool $isStudent): void
    {
        $this->setRole(self::ROLE_STUDENT, $isStudent);
    }

    public function setIsDozent(bool $isDozent): void
    {
        $this->setRole(self::ROLE_DOZENT, $isDozent);
    }

    public function setIsSSOUser(bool $isSSOUser): void
    {
        $this->setRole(self::ROLE_SSO_USER, $isSSOUser);
    }

    public function isAdmin(): bool
    {
        return in_array(self::ROLE_ADMIN, $this->roles);
    }

    public function isStudent(): bool
    {
        return in_array(self::ROLE_STUDENT, $this->roles);
    }

    public function isDozent(): bool
    {
        return in_array(self::ROLE_DOZENT, $this->roles);
    }

    public function isSSOUser(): bool
    {
        return in_array(self::ROLE_SSO_USER, $this->roles);
    }

    public function getDataPrivacyAccepted(): ?bool
    {
        return $this->dataPrivacyAccepted;
    }

    public function setDataPrivacyAccepted(bool $dataPrivacyAccepted): self
    {
        $this->dataPrivacyAccepted = $dataPrivacyAccepted;
        return $this;
    }

    public function getDataPrivacyVersion(): int
    {
        return $this->dataPrivacyVersion;
    }

    public function setDataPrivacyVersion(int $dataPrivacyVersion): void
    {
        $this->dataPrivacyVersion = $dataPrivacyVersion;
    }

    public function acceptedCurrentDataPrivacy(): bool
    {
        return $this->dataPrivacyAccepted && $this->dataPrivacyVersion >= DataPrivacyVoter::DATA_PRIVACY_VERSION;
    }

    public function getTermsOfUseAccepted(): ?bool
    {
        return $this->termsOfUseAccepted;
    }

    public function setTermsOfUseAccepted(bool $termsOfUseAccepted): self
    {
        $this->termsOfUseAccepted = $termsOfUseAccepted;
        return $this;
    }

    public function getTermsOfUseVersion(): int
    {
        return $this->termsOfUseVersion;
    }

    public function setTermsOfUseVersion(int $termsOfUseVersion): void
    {
        $this->termsOfUseVersion = $termsOfUseVersion;
    }

    public function acceptedCurrentTermsOfUse(): bool
    {
        return $this->termsOfUseAccepted && $this->termsOfUseVersion >= TermsOfUseVoter::TERMS_OF_USE_VERSION;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpirationDate(): DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(DateTimeImmutable $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isExpirationNoticeSent(): bool
    {
        return $this->expirationNoticeSent;
    }

    public function setExpirationNoticeSent(bool $expirationNoticeSent): void
    {
        $this->expirationNoticeSent = $expirationNoticeSent;
    }

    /**
     * @return Collection<Course>
     */
    public function getCourses(): Collection
    {
        return $this->getCourseRoles()->map(function (CourseRole $courseRole) {
            return $courseRole->getCourse();
        });
    }

    /**
     * @return Collection<Exercise>
     */
    public function getCreatedExercises(): Collection
    {
        return $this->createdExercises;
    }

    public function isAnonymized(): bool
    {
        return $this->isAnonymized;
    }

    public function setIsAnonymized(bool $isAnonymized): void
    {
        $this->isAnonymized = $isAnonymized;
    }

    private function setRole(string $roleToSet, bool $set): void
    {
        if ($set) {
            $this->roles[] = $roleToSet;
        } else {
            $this->roles = array_filter($this->roles, function ($role) use ($roleToSet) {
                return $role !== $roleToSet;
            });
        }
    }
}
