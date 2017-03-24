<?php

class UrlGenerator
{
    /**
     * Gets url parameters from filters.
     * Format: a=a&b[0]=b1&b[1]=b2&...
     * @param Filters $filters Filters to convert to parameters
     * @return string Parameter string.
     */
    public function getParameters(Filters $filters)
    {
        $result = implode('&', [
            'action=' . $filters->getAction(),
            http_build_query($filters->getIntegerFields()),
            http_build_query($filters->getBooleanFields()),
            http_build_query($filters->getFloatFields()),
            http_build_query($filters->getStringFields()),
            $this->getPriceParameters($filters->getPriceFields()),
            $this->getDistanceParameters($filters->getDistanceFields()),
        ]);

        // Remove multiple &&
        while (strpos($result, '&&') !== false) {
            $result = str_replace('&&', '&', $result);
        }
        return trim($result, '&');
    }

    private function getPriceParameters($priceFields)
    {
        return $this->getParamsForEnums($priceFields, 'Price');
    }

    private function getDistanceParameters($priceFields)
    {
        return $this->getParamsForEnums($priceFields, 'Distance');
    }

    private function getParamsForEnums($priceFields, $className)
    {
        $params = [];
        foreach ($priceFields as $fieldName => $rawValue) {
            $class = new ReflectionClass($className);
            foreach ($class->getConstants() as $name => $value) {
                if ($value > 0 && $value === $rawValue) {
                    array_push($params, $fieldName . '=' . strtolower($name));
                }
            }
        }

        return implode('&', $params);
    }
}