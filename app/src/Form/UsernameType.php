<?php
/**
 * Username type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UsernameType
 *
 * @package Form
 */
class UsernameType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder The form builder
     * @param array                                        $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'user_id',
            TextType::class,
            [
                'label' => 'label.login',
                'required' => true,
                'attr' => [
                    'max_length' => 30,
                    'placeholder' => 'placeholder.message_username',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => ['username-default'],
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['username-default'],
                            'max' => 30,
                        ]
                    ),
                ],
            ]
        )->add(
            'save',
            SubmitType::class,
            [
                'label' => 'action.send_invitation',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ]
        );
        $builder->get('user_id')->addModelTransformer(new UserIdToUsernameTransformer($options['user_repository']));
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
                'validation_groups' => 'username-default',
                'user_repository' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'username_type';
    }
}
