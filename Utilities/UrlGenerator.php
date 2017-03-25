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
            $this->getFilterParameter($filters->getFiltersByType(int::class)),
            $this->getFilterParameter($filters->getFiltersByType(bool::class)),
            $this->getFilterParameter($filters->getFiltersByType(float::class)),
            $this->getFilterParameter($filters->getFiltersByType(string::class)),
            $this->getPriceParameters($filters->getFiltersByType(Price::class)),
            $this->getDistanceParameters($filters->getFiltersByType(Distance::class)),
        ]);

        // Remove multiple &&
        while (strpos($result, '&&') !== false) {
            $result = str_replace('&&', '&', $result);
        }
        return trim($result, '&');
    }

    private function getPriceParameters($filters)
    {
        return $this->getParamsForEnums($filters, 'Price');
    }

    private function getDistanceParameters($filters)
    {
        return $this->getParamsForEnums($filters, 'Distance');
    }

    private function getParamsForEnums($filters, $className)
    {
        $params = [];
        foreach ($filters as $filter) {
            $filterName = $filter->getName();
            $rawValue = $filter->getValue();
            $class = new ReflectionClass($className);
            foreach ($class->getConstants() as $name => $value) {
                if ($value > 0 && $value === $rawValue) {
                    array_push($params, $filterName . '=' . strtolower($name));
                }
            }
        }

        return implode('&', $params);
    }

    private function getFilterParameter($fields)
    {
        $params = [];
        foreach ($fields as $field) {
            $value = $field->getValue();
            $name = $field->getName();
            if (is_array($value)) {
                foreach ($value as $v) {
                    array_push($params, $name . '[]=' . $v);
                }
            } else {
                array_push($params, $name . '=' . $value);
            }
        }

        return implode('&', $params);
    }
}