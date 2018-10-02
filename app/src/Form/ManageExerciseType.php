<?php
/**
 * Manage exercise type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ManageExerciseType
 *
 * @package Form
 */
class ManageExerciseType extends AbstractType
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
                    'required' => false,
                    'attr' => [
                        'max_length' => 128,
                    ],
                    'constraints' => [
                        new Assert\Length(
                            [
                                'groups' => ['manage-exercise-default'],
                                'min' => '1',
                                'max' => '128',
                            ]
                        ),
                    ],
                ]
            ) ->add(
                'record',
                TextType::class,
                [
                    'label' => 'label.record',
                    'required' => false,
                    'constraints' => [
                        new Assert\Range(
                            [
                                'groups' => ['manage-exercise-default'],
                                'min' => '0',
                                'max' => '1000',
                            ]
                        ),
                        new Assert\Regex(
                            [
                                'groups' => ['manage-exercise-default'],
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
                'validation_groups' => 'manage-exercise-default',
                'exercise_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'manage_exercise_type';
    }
}
