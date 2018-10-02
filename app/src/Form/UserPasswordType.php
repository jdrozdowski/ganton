<?php
/**
 * User password type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserPasswordType
 *
 * @package Form
 */
class UserPasswordType extends AbstractType
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
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'trim' => true,
                    'required' => true,
                    'attr' => [
                        'max_length' => 32,
                    ],
                    'constraints' => [
                        new Assert\NotBlank(
                            [
                                'groups' => ['password-default'],
                            ]
                        ),
                        new Assert\Length(
                            [
                                'groups' => ['password-default'],
                                'min' => 8,
                                'max' => 32,
                            ]
                        ),
                    ],
                ]
            )->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'action.change_password',
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
                'validation_groups' => 'password-default',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'password-type';
    }
}
