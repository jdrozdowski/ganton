<?php
/**
 * Unique Login validator.
 */
namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniqueLoginValidator
 *
 * @package Validator\Constraints
 */
class UniqueLoginValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed                                   $value      The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint->repository) {
            return;
        }

        $result = $constraint->repository->findForUniqueness($value);

        if ($result && count($result)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ login }}', $value)
                ->addViolation();
        }
    }
}
