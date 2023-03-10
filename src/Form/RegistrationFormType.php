<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder        
            ->add('email' , EmailType::class, [
                'label' => 'Adresse email :',
                'attr'  => [
                    'placeholder' => 'example@email.fr',
                    'class' => 'form-control mb-3'
                ],            

            ])
            ->add('username', null, [
                'label' => 'Nom d\'utilisateur :',
                'attr'  => [
                    'placeholder' => 'Username',
                    'class' => 'form-control mb-3'
                ],         
            ])
            ->add('userType', ChoiceType::class, [
                'label' => 'Pourquoi suis-je ici ?',
                'attr' => [
                    'class' => 'form-control mb-3'
                ],
                'choices'  => [
                    'Je cherche un emploi' => 'candidate',
                    'Je veux poster des offres d\'emploi' => 'company',                    
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'attr' => [
                    'class' => 'ms-2 pt-2'
                ],
                'label' => 'Accepter les conditions d\'utilisation',                
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Veuillez accepter les termes d\'utilisation.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Mot de passe :',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control mb-3',
                    'placeholder' => '********'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caract??res',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
