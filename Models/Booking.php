<?php
class Booking
{
    private $id;
    private $fields = [];
    private $fieldsByType = [];

    /**
     * Booking constructor.
     * @param $id int ID of the booking
     * @param $dataTypeCluster DataTypeCluster Data type cluster of  the booking data.
     */
    public function __construct(int $id, DataTypeCluster $dataTypeCluster)
    {
        $this->id = $id;

        $this->fieldsByType[IntegerField::Type()] = $dataTypeCluster->getIntegerFields();
        $this->fieldsByType[BooleanField::Type()] = $dataTypeCluster->getBooleanFields();
        $this->fieldsByType[FloatField::Type()] = $dataTypeCluster->getFloatFields();
        $this->fieldsByType[StringField::Type()] = $dataTypeCluster->getStringFields();
        $this->fieldsByType[PriceField::Type()] = $dataTypeCluster->getPriceFields();
        $this->fieldsByType[DistanceField::Type()] = $dataTypeCluster->getDistanceFields();
        $this->fieldsByType[DayOfWeekField::Type()] = $dataTypeCluster->getDayOfWeekFields();
        $this->fieldsByType[MonthOfYearField::Type()] = $dataTypeCluster->getMonthOfYearFields();

        $this->fields = array_merge($this->fieldsByType[IntegerField::Type()],
            $this->fieldsByType[BooleanField::Type()],
            $this->fieldsByType[FloatField::Type()],
            $this->fieldsByType[StringField::Type()],
            $this->fieldsByType[PriceField::Type()],
            $this->fieldsByType[DistanceField::Type()],
            $this->fieldsByType[DayOfWeekField::Type()],
            $this->fieldsByType[MonthOfYearField::Type()]);
    }

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get fields by a given type.
     * @param string $type Field type to get.
     * @return Field[] Fields for the given type.
     */
    public function getFieldsByType(string $type) {
        return $this->fieldsByType[$type];
    }

    /**
     * Gets a filter by a name.
     * @param string $name Name of the filter to get.
     * @return Field Field which matches the name.
     * @throws Exception If field with the given name can not be found.
     */
    public function getFieldByName(string $name) : Field {
        return $this->fields[$name];
    }

    /**
     * Gets fields by matching name and value.
     * @param array $fieldNameAndValue Array of field names and values to check.
     * @return array Fields which matches $fieldNames
     */
    public function getFieldsByNamesAndValue($fieldNameAndValue) {
        $fields = [];
        foreach ($fieldNameAndValue as $key => $value) {
            $field = $this->getFieldByName($key);
            if ($field->getValue() == $value) {
                $fields[] = $field;
            }

        }
        return $fields;
    }
}