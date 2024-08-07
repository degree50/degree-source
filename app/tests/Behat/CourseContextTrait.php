<?php

namespace App\Tests\Behat;

use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

trait CourseContextTrait
{
    /**
     * Creates a new course with the given ID and adds a course role "DOZENT"
     * for the currently logged in user (use the according step to log in first)
     *
     * @Given I have a course with ID :courseId
     */
    public function iHaveACourseWithIDForUser($courseId): void
    {
        $course = new Course($courseId);
        $course->setName($courseId);
        $courseRole = new CourseRole();
        $courseRole->setUser($this->currentUser);
        $courseRole->setCourse($course);
        $courseRole->setName(CourseRole::DOZENT);

        $this->entityManager->persist($courseRole);

        $course->addCourseRole($courseRole);

        $this->entityManager->persist($course);
        $this->entityManager->flush();
    }

    /**
     * Ensures a course with the given ID exists without any course roles
     *
     * @Given A Course with ID :courseId exists
     */
    public function ensureCourseExists($courseId): Course
    {
        /** @var Course $course */
        $course = $this->entityManager->find(Course::class, $courseId);

        if (!$course) {
            $course = new Course($courseId);
            $course->setName($courseId);

            $this->entityManager->persist($course);
            $this->entityManager->flush();
        }

        return $course;
    }

    /**
     * @Then The Course :courseId does exist
     */
    public function theCourseWithIdDoesExist($courseId): void
    {
        $course = $this->entityManager->find(Course::class, $courseId);
        assertNotNull($course);
    }

    /**
     * @Then The Course :courseId does not exist
     */
    public function theCourseWithIdDoesNotExist($courseId): void
    {
        /** @var Course|null $course */
        $course = $this->entityManager->find(Course::class, $courseId);
        assertEquals(null, $course);
    }
}
