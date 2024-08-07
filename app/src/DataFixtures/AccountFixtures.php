<?php

namespace App\DataFixtures;

use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\Fachbereich\Model\Fachbereich;
use App\Domain\User\Model\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountFixtures extends Fixture
{
    public const string COURSE_REFERENCE = 'course';
    public const string CREATOR_REFERENCE = 'creator';

    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $fachbereich = $this->createFachbereich($manager, 'Fachbereich Mathematik');

        $course = $this->createCourse($manager, 'Seminar Mathe 1', $fachbereich);
        $course2 = $this->createCourse($manager, 'Seminar Mathe 2', $fachbereich);

        // other fixtures can get this object using the AccountFixtures::COURSE_REFERENCE constant
        $this->addReference(self::COURSE_REFERENCE, $course);

        $admin = $this->createUser($manager, 'admin@sandstorm.de', [User::ROLE_ADMIN]);
        $this->addReference(self::CREATOR_REFERENCE, $admin);

        // dozent user
        $dozent = $this->createUser($manager, 'dozent@sandstorm.de', [USER::ROLE_DOZENT]);
        $this->createCourseRole($manager, CourseRole::DOZENT, $course, $dozent);

        // student user
        $student = $this->createUser($manager, 'student@sandstorm.de', [User::ROLE_STUDENT]);
        $this->createCourseRole($manager, CourseRole::STUDENT, $course, $student);

        $student2 = $this->createUser($manager, 'student2@sandstorm.de', [User::ROLE_STUDENT]);
        $this->createCourseRole($manager, CourseRole::STUDENT, $course, $student2);

        $this->createRandomStudentUsers($manager, 10);

        $manager->flush();
    }

    private function createRandomStudentUsers(ObjectManager $manager, int $amount): void
    {
        for ($i = 0; $i < $amount; $i++) {
            $this->createUser($manager, 'another-student' . $i . '@sandstorm.de', [User::ROLE_STUDENT]);
        }
    }

    private function createUser(ObjectManager $manager, string $userName, $roles = []): User
    {
        $account = new User();
        $account->setEmail($userName);
        $account->setRoles($roles);
        $account->setPassword($this->userPasswordHasher->hashPassword($account, 'password'));
        $account->setIsVerified(true);
        $manager->persist($account);

        return $account;
    }

    private function createCourse(ObjectManager $manager, string $name, Fachbereich $fachbereich): Course
    {
        $course = new Course();
        $course->setName($name);
        $course->setFachbereich($fachbereich);
        $manager->persist($course);

        return $course;
    }

    private function createCourseRole(ObjectManager $manager, string $name, Course $course, User $user): void
    {
        $courseRole = new CourseRole();
        $courseRole->setName($name);
        $courseRole->setCourse($course);
        $courseRole->setUser($user);
        $manager->persist($courseRole);

    }

    private function createFachbereich(ObjectManager $manager, string $name): Fachbereich
    {
        $fachbereich = new Fachbereich();
        $fachbereich->setName($name);

        $manager->persist($fachbereich);

        return $fachbereich;
    }
}
