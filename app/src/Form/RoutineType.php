<?php
/**
 * Routine type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RoutineType.
 *
 * @package Form
 */
class RoutineType extends AbstractType
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
                    'label' => 'label.name',
                    'required' => true,
                    'attr' => [
                        'max_length' => 128,
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            [
                                'groups' => ['routine-default'],
                            ]
                        ),
                        new Assert\Length(
                            [
                                'groups' => ['routine-default'],
                                'min' => '3',
                                'max' => '128',
                            ]
                        ),
                    ],
                ]
            )->add(
                'is_public',
                ChoiceType::class,
                [
                    'label' => 'label.is_public',
                    'required' => true,
                    'choices' => [
                        'label.public' => 1,
                        'label.private' => 0,
                    ],
                    'expanded' => false,
                    'multiple' => false,
                    'constraints' => [
                        new Assert\Choice(
                            [
                                'groups' => ['routine-default'],
                                'choices' => [1, 0],
                            ]
                        ),
                    ],
                ]
            )->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'action.confirm',
                    'attr' => [
                        'class' => 'btn btn-success',
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
                'validation_groups' => 'routine-default',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'routine_type';
    }
}
