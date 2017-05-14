<?php

class DistanceMeasurement
{
    /**
     * @var int
     */
    private $gamma;

    /**
     * Distance constructor.
     * @param ConfigProvider $config
     */
    public function __construct(ConfigProvider $config)
    {
        $this->gamma = $config->get('gamma');
        $this->ignoreFields = $config->get('ignoreFields');
    }

    /**
     * Measures the distance between two bookings.
     * @param Booking $from From booking.
     * @param Booking $to To booking
     * @return float Distance from the provided booking to the prototype.
     */
    public function measure(Booking $from, Booking $to): float
    {
        $fromNumericFields = $from->getFieldsByType(IntegerField::Type());
        $numericDistance = 0;
        foreach ($fromNumericFields as $fromNumericField) {
            if (in_array($fromNumericField->getName(), $this->ignoreFields)) {
                continue;
            }
            $toField = $to->getFieldByName($fromNumericField->getName());
            $numericDistance += pow($toField->getValue() - $fromNumericField->getValue(), 2);
        }

        $fromCategoricFields = array_merge(
            $from->getFieldsByType(BooleanField::Type()),
            $from->getFieldsByType(DistanceField::Type()),
            $from->getFieldsByType(PriceField::Type())
        );
        $categoricDistance = 0;
        foreach ($fromCategoricFields as $fromCategoricField) {
            if (in_array($fromCategoricField->getName(), $this->ignoreFields)) {
                continue;
            }
            $toField = $to->getFieldByName($fromCategoricField->getName());
            $categoricDistance += $toField->getValue() != $fromCategoricField->getValue()
                ? 1
                : 0;
        }

        return $numericDistance + $this->gamma * $categoricDistance;
    }
}