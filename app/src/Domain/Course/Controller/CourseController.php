<?php

namespace App\Domain\Course\Controller;

use App\Domain\Course\Form\CourseFormType;
use App\Domain\Course\Form\CourseMembersType;
use App\Domain\Course\Model\Course;
use App\Domain\CourseRole\Model\CourseRole;
use App\Domain\User\Model\User;
use App\Security\Voter\CourseVoter;
use App\Security\Voter\DataPrivacyVoter;
use App\Security\Voter\TermsOfUseVoter;
use App\Security\Voter\UserVerifiedVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)]
#[IsGranted(UserVerifiedVoter::USER_VERIFIED)]
#[IsGranted(DataPrivacyVoter::ACCEPTED)]
#[IsGranted(TermsOfUseVoter::ACCEPTED)]
class CourseController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface    $translator,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[IsGranted(CourseVoter::EDIT_MEMBERS, subject: "course")]
    #[Route("/exercise-overview/{id}/course-members", name: "exercise-overview__course--members")]
    public function editCourseMembers(Request $request, Course $course = null): Response
    {
        if (!$course) {
            return $this->render("Security/403.html.twig");
        }

        $form = $this->createForm(CourseMembersType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMembers = $form->get('users')->getData();

            /** @var User $newMember */
            foreach ($newMembers as $newMember) {
                $courseRole = new CourseRole();
                $courseRole->setName(CourseRole::STUDENT);
                $courseRole->setUser($newMember);
                $course->addCourseRole($courseRole);
            }

            $this->entityManager->persist($course);
            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('course.editMembers.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute('exercise-overview__course--members', ['id' => $course->getId()]);
        }

        $studentsArray = $course->getCourseRoles()
            ->filter(function (CourseRole $role) {
                if ($role->getUser()->isAdmin()) {
                    return false;
                }
                return $role->getUser()->isStudent();
            })
            ->toArray();

        // MUTATION! Sort by userName
        usort($studentsArray, function (CourseRole $a, CourseRole $b) {
            return ($a->getUser()->getUsername() > $b->getUser()->getUsername()) ? -1 : 1;
        });

        $students = new ArrayCollection($studentsArray);

        return $this->render('Course/Members.html.twig', [
            'course' => $course,
            'students' => $students,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(CourseVoter::EDIT_MEMBERS, subject: "course")]
    #[Route("/exercise-overview/{id}/course-members/{userRole_id}/remove", name: "exercise-overview__course--remove-role")]
    public function removeCourseMember(
        Request    $request,
        Course     $course = null,
        #[MapEntity(id: "userRole_id")]
        CourseRole $courseRole = null,
    ): Response
    {
        if (!$course || !$courseRole) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $redirectToEdit = (bool)$request->get('redirectToEdit');

        $courseRolesWithDozent = $course->getCourseRoles()->filter(fn(CourseRole $role) => $role->isCourseDozent());
        if ($redirectToEdit && count($courseRolesWithDozent) == 1) {
            $this->addFlash(
                'danger',
                $this->translator->trans('course.removeMember.messages.notPossible', [], 'DegreeBase')
            );

            return $this->redirectToRoute('exercise-overview__course--edit', ['id' => $course->getId()]);
        }

        $this->entityManager->remove($courseRole);
        $this->entityManager->flush();

        if ($redirectToEdit) {
            return $this->redirectToRoute('exercise-overview__course--edit', ['id' => $course->getId()]);
        } else {
            return $this->redirectToRoute('exercise-overview__course--members', ['id' => $course->getId()]);
        }
    }

    #[IsGranted(CourseVoter::EDIT_MEMBERS, subject: "course")]
    #[Route("/exercise-overview/{id}/course-members/{userRole_id}/upgrade", name: "exercise-overview__course--upgrade-role")]
    public function upgradeCourseMember(
        Course     $course = null,
        #[MapEntity(id: "userRole_id")]
        CourseRole $courseRole = null,
    ): Response
    {
        if (!$course || !$courseRole) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $courseRole->setName(CourseRole::DOZENT);

        $this->entityManager->persist($courseRole);
        $this->entityManager->flush();

        return $this->redirectToRoute('exercise-overview__course--members', ['id' => $course->getId()]);
    }

    #[IsGranted(CourseVoter::EDIT_MEMBERS, subject: "course")]
    #[Route("/exercise-overview/{id}/course-members/{userRole_id}/downgrade", name: "exercise-overview__course--downgrade-role")]
    public function downgradeCourseMember(
        Course     $course = null,
        #[MapEntity(id: "userRole_id")]
        CourseRole $courseRole = null,
    ): Response
    {
        if (!$course || !$courseRole) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $courseRole->setName(CourseRole::STUDENT);

        $this->entityManager->persist($courseRole);
        $this->entityManager->flush();

        return $this->redirectToRoute('exercise-overview__course--members', ['id' => $course->getId()]);
    }

    #[IsGranted(CourseVoter::CREATE)]
    #[Route("/exercise-overview/course/new", name: "exercise-overview__course--new")]
    public function new(Request $request): Response
    {
        $course = new Course();

        $form = $this->createForm(CourseFormType::class, $course);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $newMembers = $form->get('users')->getData();
            if (count($newMembers) > 0) {
                $this->createOrUpdateCourse($form);

                $this->addFlash(
                    'success',
                    $this->translator->trans('course.new.messages.success', [], 'DegreeBase')
                );

                return $this->redirectToRoute('exercise-overview', ['id' => $course->getId()]);
            }

            $this->addFlash(
                'danger',
                $this->translator->trans('course.new.messages.missingDozent', [], 'DegreeBase'),
            );
        }

        return $this->render('Course/New.html.twig', [
            'course' => $course,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(CourseVoter::EDIT, subject: "course")]
    #[Route("/exercise-overview/course/edit/{id}", name: "exercise-overview__course--edit")]
    public function edit(Request $request, Course $course = null): Response
    {
        if (!$course) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $form = $this->createForm(CourseFormType::class, $course);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->createOrUpdateCourse($form);

            $this->addFlash(
                'success',
                $this->translator->trans('course.edit.messages.success', [], 'DegreeBase')
            );

            return $this->redirectToRoute('exercise-overview__course--edit', ['id' => $course->getId()]);
        }

        $tutorsArray = $course->getCourseRoles()
            ->filter(function (CourseRole $role) {
                return $role->getUser()->isDozent();
            })
            ->toArray();

        // MUTATION! Sort by userName
        usort($tutorsArray, function (CourseRole $a, CourseRole $b) {
            return ($a->getUser()->getUsername() > $b->getUser()->getUsername()) ? -1 : 1;
        });

        $tutors = new ArrayCollection($tutorsArray);

        return $this->render('Course/Edit.html.twig', [
            'course' => $course,
            'tutors' => $tutors,
            'form' => $form->createView()
        ]);
    }

    #[IsGranted(CourseVoter::DELETE, subject: "course")]
    #[Route("/exercise-overview/course/delete/{id}", name: "exercise-overview__course--delete")]
    public function delete(Course $course = null): Response
    {
        if (!$course) {
            return $this->render('Security/403.html.twig')->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        if (count($course->getExercises()) > 0) {
            $this->addFlash(
                'danger',
                $this->translator->trans('course.delete.messages.notPossible', [], 'DegreeBase')
            );

            return $this->redirectToRoute('exercise-overview', ['id' => $course->getId()]);
        }

        $this->entityManager->remove($course);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('course.delete.messages.success', [], 'DegreeBase')
        );

        return $this->redirectToRoute('exercise-overview');
    }

    private function createOrUpdateCourse(FormInterface $form): void
    {
        // $form->getData() holds the submitted values
        // but, the original `$course` variable has also been updated
        $course = $form->getData();

        $newMembers = $form->get('users')->getData();

        /** @var User $newMember */
        foreach ($newMembers as $newMember) {
            $courseRole = new CourseRole();
            $courseRole->setName(CourseRole::DOZENT);
            $courseRole->setUser($newMember);
            $course->addCourseRole($courseRole);
        }

        $this->entityManager->persist($course);
        $this->entityManager->flush();
    }
}
