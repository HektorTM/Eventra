<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
class EventCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter an event title'),
                ],
            ])

            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 5],
            ])

            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a category',
                'required' => false,
            ])

            ->add('date', DateTimeType::class, [
                'label' => 'Date & time',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(message: 'Please choose a date and time'),
                ],
            ])

            ->add('location', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter a location'),
                ],
            ])

            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Event image',
                'constraints' => [
                    new Assert\Image(
                        maxSize: '4M',
                        mimeTypesMessage: 'Please upload a valid image file'
                    ),
                ],
            ])

            ->add('isPublished', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => false,
                    'Published' => true,
                ],
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
