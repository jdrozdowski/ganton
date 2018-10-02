<?php
/**
 * Exercise type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExerciseType
 *
 * @package Form
 */
class ExerciseType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder The form builder
     * @param array                                        $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'label.exercise_name',
                    'required' => true,
                    'attr' => [
                        'max_length' => 128,
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            [
                                'groups' => ['exercise-default'],
                            ]
                        ),
                        new Assert\Length(
                            [
                                'groups' => ['exercise-default'],
                                'min' => '1',
                                'max' => '128',
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'sets',
                IntegerType::class,
                [
                    'label' => 'label.sets',
                    'required' => true,
                    'attr' => [
                        'min' => 1,
                        'max' => 1000,
                    ],

                    'constraints' => [
                        new Assert\NotBlank(
                            [
                                'groups' => ['exercise-default'],
                            ]
                        ),
                        new Assert\Range(
                            [
                                'groups' => ['exercise-default'],
                                'min' => '1',
                                'max' => '1000',
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'reps',
                IntegerType::class,
                [
                    'label' => 'label.reps',
                    'required' => true,
                    'attr' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            [
                                'groups' => ['exercise-default'],
                            ]
                        ),
                        new Assert\Range(
                            [
                                'groups' => ['exercise-default'],
                                'min' => '1',
                                'max' => '1000',
                            ]
                        ),
                    ],
                ]
            ) ->add(
                'weight',
                TextType::class,
                [
                    'label' => 'label.weight',
                    'required' => false,
                    'constraints' => [
                        new Assert\Range(
                            [
                                'groups' => ['exercise-default'],
                                'min' => '0',
                                'max' => '1000',
                            ]
                        ),
                        new Assert\Regex(
                            [
                                'groups' => ['exercise-default'],
                                'pattern' => '/[0-9]+(\.[0-9][0-9]?)?/',
                            ]
                        ),
                    ],
                ]
            );
    }

    /**
     * Configures the options for this type.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'exercise-default',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'exercise_type';
    }
}
