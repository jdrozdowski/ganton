<?php
/**
 * Workout type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WorkoutType
 *
 * @package Form
 */
class WorkoutType extends AbstractType
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
                'due_date',
                DateTimeType::class,
                [
                    'label' => 'label.due_date',
                    'input'  => 'datetime',
                    'widget' => 'choice',
                    'years' => [2016, 2017, 2018, 2019, 2020],
                    'date_format' => 'yyyy-MMM-dd hh:mm: a',
                    'model_timezone' => 'UTC',
                    'view_timezone' => 'UTC',
                ]
            )
            ->add(
                'exercises',
                CollectionType::class,
                [
                    'label' => 'label.exercises',
                    'entry_type' => ExerciseType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'prototype_name' => 'exercise__name__',
                    'label_attr' => [
                        'class' => 'sr-only',
                    ],
                ]
            )->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'label.add',
                    'attr' => [
                        'class' => 'btn btn-success',
                    ],
                ]
            );
        $builder->get('due_date')->addModelTransformer(new DateTimeTransformer());
        $builder->get('exercises')->addModelTransformer(new ExercisesDataTransformer($options['exercise_repository']));
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
                'validation_groups' => 'workout-default',
                'exercise_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'workout_type';
    }
}
