<?php

namespace App\Domain\ResetPassword\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResetPasswordRequestFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'translation_domain' => 'UserPasswordReset',
                'label' => 'form.label.email',
                'attr' => ['autocomplete' => 'username'],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('form.validation.email-blank', [], 'UserRegistration'),
                    ]),
                    new Email([
                        'message' => $this->translator->trans('form.validation.email-invalid', [], 'UserRegistration'),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
