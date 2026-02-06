<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre de l\'article',
                    'required' => true,
                    'minlength' => 3,
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre ne peut pas être vide']),
                    new Length(['min' => 3, 'max' => 255, 'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères']),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Entrez le contenu de l\'article',
                    'required' => true,
                    'minlength' => 10,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le contenu ne peut pas être vide']),
                    new Length(['min' => 10, 'minMessage' => 'Le contenu doit contenir au moins {{ limit }} caractères']),
                ],
            ])
            ->add('picture', TextType::class, [
                'label' => 'URL de l\'image',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com/image.jpg',
                    'required' => true,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L\'URL de l\'image ne peut pas être vide']),
                    new Url(['message' => 'L\'URL de l\'image n\'est pas valide']),
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                    'required' => true,
                ],
                'placeholder' => '-- Sélectionner une catégorie --',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une catégorie']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'submit_label' => 'Envoyer',
        ]);
    }
}
