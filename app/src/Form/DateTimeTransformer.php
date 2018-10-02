<?php
/**
 * Datetime transformer.
 */
namespace Form;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class DateTimeTransformer
 *
 * @package Form
 */
class DateTimeTransformer implements DataTransformerInterface
{
    /**
     * Transform datetime database format to datetime form format.
     *
     * @param mixed $datetime
     *
     * @return \DateTime
     */
    public function transform($datetime)
    {
        return new \DateTime($datetime);
    }

    /**
     * Transform datetime form format to datetime database format.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function reverseTransform($value)
    {
        return $value->format('Y-m-d H:i:s');
    }
}
