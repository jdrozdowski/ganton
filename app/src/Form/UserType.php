<?php
/**
 * User type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserType
 *
 * @package Form
 */
class UserType extends AbstractType
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
                'firstname',
                TextType::class,
                [
                    'label' => 'label.firstname',
                    'required' => false,
                    'attr' => [
                        'max_length' => 128,
                    ],
                    'constraints' => [
                        new Assert\Length(
                            [
                                'groups' => ['user-default'],
                                'min' => '2',
                                'max' => '128',
                            ]
                        ),
                    ],
                ]
            )->add(
                'surname',
                TextType::class,
                [
                    'label' => 'label.surname',
                    'required' => false,
                    'attr' => [
                        'max_length' => 128,
                    ],
                    'constraints' => [
                        new Assert\Length(
                            [
                                'groups' => ['user-default'],
                                'min' => '2',
                                'max' => '128',
                            ]
                        ),
                    ],
                ]
            )->add(
                'location',
                TextType::class,
                [
                    'label' => 'label.location',
                    'required' => false,
                    'attr' => [
                        'max_length' => 128,
                    ],
                    'constraints' => [
                        new Assert\Length(
                            [
                                'groups' => ['user-default'],
                                'min' => '3',
                                'max' => '128',
                            ]
                        ),
                    ],
                ]
            )->add(
                'birthdate',
                DateType::class,
                [
                    'label' => 'label.birthdate',
                    'input'  => 'datetime',
                    'widget' => 'choice',
                    'years' => range(1917, 2017),
                    'model_timezone' => 'UTC',
                    'view_timezone' => 'UTC',
                ]
            )->add(
                'height',
                IntegerType::class,
                [
                    'label' => 'label.height',
                    'required' => false,
                    'attr' => [
                        'min' => 1,
                        'max' => 300,
                    ],
                    'constraints' => [
                        new Assert\Range(
                            [
                                'groups' => ['user-default'],
                                'min' => '1',
                                'max' => '300',
                            ]
                        ),
                    ],
                ]
            )->add(
                'weight',
                NumberType::class,
                [
                    'label' => 'label.user_weight',
                    'required' => false,
                    'scale' => 2,
                    'attr' => [
                        'min' => 1,
                        'max' => 300,
                    ],
                    'constraints' => [
                        new Assert\Range(
                            [
                                'groups' => ['user-default'],
                                'min' => '1',
                                'max' => '300',
                            ]
                        ),
                    ],
                ]
            )->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'action.edit',
                    'attr' => [
                        'class' => 'btn btn-success',
                    ],
                ]
            );
        $builder->get('birthdate')->addModelTransformer(new DateTimeTransformer());
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
                'validation_groups' => 'user-default',
                'user_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_type';
    }
}
