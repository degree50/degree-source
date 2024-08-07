<?php

namespace App\Administration\Form;

use App\Domain\CourseRole\Model\CourseRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InlineCourseRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user')
            ->add('name', ChoiceType::class, [
                'choices' => [
                    'Lernende*r' => CourseRole::STUDENT,
                    'Lehrende*r' => CourseRole::DOZENT,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CourseRole::class,
        ]);
    }
}
