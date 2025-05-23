<?php

declare (strict_types=1);
namespace Convo\Core\Workflow;

/**
 * For Dialogflow and Alexa requests or any other platform which is intent based.
 * 
 * @author Tole
 *
 */
interface IIntentAwareRequest extends \Convo\Core\Workflow\IConvoRequest
{
    /**
     * Returns intent name
     * @return string
     */
    public function getIntentName();
    /**
     * Returns the collected slot values
     * @return array Filled slot values as $key => $value pairs
     */
    public function getSlotValues();
    /**
     * Returns raw slots
     * @return array Raw slot data as associative array
     */
    public function getRawSlots();
    /**
     * Get the request's platform ID
     * @return string Platform ID for the request
     */
    public function getIntentPlatformId();
}
