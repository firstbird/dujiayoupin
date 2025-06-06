<?php

namespace Convo\Pckg\Appointments;

use Convo\Core\Adapters\Alexa\Api\AlexaSettingsApi;
use Convo\Core\Workflow\IConversationElement;
use Convo\Core\Workflow\IConvoRequest;
use Convo\Core\Workflow\IConvoResponse;
use Convo\Core\Params\IServiceParamsScope;
class CancelAppointmentElement extends \Convo\Pckg\Appointments\AbstractAppointmentElement
{
    /**
     * @var string
     */
    private $_appointmentId;
    /**
     * @var string
     */
    private $_email;
    /**
     * @var string
     */
    private $_resultVar;
    /**
     * @var IConversationElement[]
     */
    private $_okFlow = array();
    /**
     * @param array $properties
     * @param AlexaSettingsApi $alexaSettingsApi
     */
    public function __construct($properties, AlexaSettingsApi $alexaSettingsApi)
    {
        parent::__construct($properties, $alexaSettingsApi);
        $this->_appointmentId = $properties['appointment_id'];
        $this->_email = $properties['email'];
        $this->_resultVar = $properties['result_var'];
        foreach ($properties['ok'] as $element) {
            $this->_okFlow[] = $element;
            $this->addChild($element);
        }
    }
    /**
     * @param IConvoRequest $request
     * @param IConvoResponse $response
     */
    public function read(IConvoRequest $request, IConvoResponse $response)
    {
        $context = $this->_getAppointmentsContext();
        $appointmentId = $this->evaluateString($this->_appointmentId);
        $email = $this->evaluateString($this->_email);
        $this->_logger->info('Canceling appointment with id [' . $appointmentId . '] for customer email [' . $email . ']');
        $data = ['existing' => $context->getAppointment($email, $appointmentId)];
        $context->cancelAppointment($email, $appointmentId);
        $this->_logger->info('Canceled appointment with id [' . $appointmentId . '] for the customers email [' . $email . ']');
        $params = $this->getService()->getComponentParams(IServiceParamsScope::SCOPE_TYPE_REQUEST, $this);
        $params->setServiceParam($this->_resultVar, $data);
        $this->_readElementsInTimezone($this->_okFlow, $request, $response);
    }
}
