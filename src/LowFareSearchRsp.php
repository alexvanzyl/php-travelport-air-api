<?php


namespace Travelport\Air;


use DOMDocument;

class LowFareSearchRsp
{
    /**
     * @var string The XML string returned from curl request.
     */
    private $response;

    /**
     * @var string
     */
    private $errorMsg = '';

    /**
     * LowFareSearchRsp constructor.
     * @param string $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * A create an xml file of the response.
     *
     * @return $this
     */
    public function createResponseXMLFile()
    {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($this->response);
        $dom->formatOutput = true;
        file_put_contents("LowFareSearchRsp.xml", $dom->saveXML());

        return $this;
    }

    /**
     * Create a SimpleXMLElement object
     *
     * We register our own namespaces just for convenience
     * in order to use xpath queries.
     *
     * @return string | \SimpleXMLElement
     */
    public function result()
    {
        $result = new \SimpleXMLElement($this->response);
        $result->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $result->registerXPathNamespace('common', 'http://www.travelport.com/schema/common_v33_0');
        $result->registerXPathNamespace('air', 'http://www.travelport.com/schema/air_v33_0');

        if ($this->hasError($result->xpath('//soap:Fault'))) {
            return $this->errorMsg;
        }

        return $result;
    }

    /**
     * Returns flight pricing and details
     *
     * @return array
     */
    public function flights()
    {
        $flights = [];
        foreach ($this->result()->xpath('//air:AirPricingSolution') as $airPricingSolution) {
            $flights[]['flight'] = [
                'details' => $this->getFlightDetails($airPricingSolution),
                'pricing' => $this->getFlightPricing($airPricingSolution)
            ];
        }

        return $flights;
    }

    /**
     * Get flight details from AirSegment
     *
     * @param \SimpleXMLElement $airPricingSolution
     *
     * @return array
     */
    public function getFlightDetails($airPricingSolution)
    {
        $flightDetails = [];
        foreach ($airPricingSolution->xpath('.//air:AirSegmentRef') as $ref) {
            $flightDetails[] = $this->parseAttributes(
                $this->getAirSegmentByKey($ref['Key'])
            );
        }

        return $flightDetails;
    }

    /**
     * Get an AirSegment by key.
     *
     * @param $key
     *
     * @return \SimpleXMLElement
     */
    public function getAirSegmentByKey($key)
    {
        return $this->result()
                   ->xpath("//air:AirSegment[@Key='$key']")[0];
    }

    /**
     * @param \SimpleXMLElement $airPricingSolution
     *
     * @return array
     */
    public function getFlightPricing($airPricingSolution)
    {
        return $this->parseAttributes(
            $airPricingSolution->xpath('.//air:AirPricingInfo')[0]
        );
    }

    /**
     * Returns an array of FlightDetails.
     *
     * @return array
     */
    public function flightDetails()
    {
        return $this->elementsToArray($this->result()->xpath('//air:FlightDetails'));
    }

    /**
     * Returns an array of FlightDetails.
     *
     * @return array
     */
    public function airSegments()
    {
        return $this->elementsToArray($this->result()->xpath('//air:AirSegment'));
    }

    /**
     * @param $xml \SimpleXMLElement
     * @return array
     */
    public function toArray($xml)
    {
        $arr = $this->parseAttributes($xml);

        foreach ($xml->children('air', true) as $parent => $child) {
            if ($child->count()) {
                $arr[$parent]  = $this->toArray($child);
            }

            $arr[$parent] = $this->parseAttributes($child);

            // If an element has no attributes we assume
            // it has only a value so let's assign it.
            if (empty($arr[$parent])) {
                $arr[$parent] = trim($child);
            }
        }

        return $arr;
    }

    /**
     * Convert all elements to an Array.
     *
     * @param $elements
     * @return array
     */
    public function elementsToArray($elements)
    {
        $converted = [];
        foreach ($elements as $element) {
            $converted[] = $this->toArray($element);
        }

        return $converted;
    }

    /**
     * Transforms the value from a SimpleXMLElement into a string.
     *
     * @param \SimpleXMLElement $object
     *
     * @return array
     */
    public function parseAttributes($object)
    {
        $attributes = [];
        foreach ($object->attributes() as $key => $val) {
            $attributes[$key] = trim($val);
        }

        return $attributes;
    }

    /**
     * Returns the original XML
     *
     * @return mixed
     */
    public function xml()
    {
        return $this->result()->asXML();
    }

    /**
     * Check if a Fault has been returned.
     *
     * @param $xpath
     *
     * @return bool
     */
    private function hasError($xpath)
    {
        if ($xpath) {
            $this->errorMsg = (string) $xpath[0]->faultstring;

            return true;
        }

        return false;
    }
}