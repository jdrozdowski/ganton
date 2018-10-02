<?php
/**
 * Manage user type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAssert;

/**
 * Class ManageUserType
 *
 * @package Form
 */
class ManageUserType extends AbstractType
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
                'login',
                TextType::class,
                [
                    'label' => 'label.login',
                    'required' => false,
                    'trim' => true,
                    'attr' => [
                        'max_length' => 30,
                    ],
                    'constraints' => [
                        new Assert\Length(
                            [
                                'groups' => ['manage-user-default'],
                                'min' => 6,
                                'max' => 30,
                            ]
                        ),
                        new Assert\Regex(
                            [
                                'groups' => ['manage-user-default'],
                                'pattern' => '/^[a-zA-Z0-9]*$/',
                            ]
                        ),
                        new CustomAssert\UniqueLogin(
                            [
                                'groups' => ['registration-default'],
                                'repository' => isset($options['user_repository']) ? $options['user_repository'] : null,
                            ]
                        ),
                    ],
                ]
            )->add(
                'role_id',
                ChoiceType::class,
                [
                    'label' => 'label.role',
                    'required' => false,
                    'choices' => [
                        'ROLE_ADMIN' => 1,
                        'ROLE_COACH' => 2,
                        'ROLE_ATHLETE' => 3,
                    ],
                    'expanded' => false,
                    'multiple' => false,
                    'constraints' => [
                        new Assert\Choice(
                            [
                                'groups' => ['manage-user-default'],
                                'choices' => [1, 2, 3],
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
                'validation_groups' => 'manage-user-default',
                'user_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'manage_user_type';
    }
}
