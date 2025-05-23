<?php

namespace Convo\Pckg\Appointments;

use Convo\Core\Params\IServiceParamsScope;
use Convo\Core\Workflow\IConversationElement;
use Convo\Core\Workflow\IConvoRequest;
use Convo\Core\Workflow\IConvoResponse;
use Convo\Core\Adapters\Alexa\Api\AlexaSettingsApi;
class UpdateAppointmentElement extends \Convo\Pckg\Appointments\AbstractAppointmentElement
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
    private $_appointmentDate;
    /**
     * @var string
     */
    private $_appointmentTime;
    /**
     * @var array
     */
    private $_payload;
    /**
     * @var string
     */
    private $_resultVar;
    /**
     * @var IConversationElement[]
     */
    private $_okFlow = array();
    /**
     * @var IConversationElement[]
     */
    private $_notAvailableFlow = array();
    /**
     * @param array $properties
     * @param AlexaSettingsApi $alexaSettingsApi
     */
    public function __construct($properties, AlexaSettingsApi $alexaSettingsApi)
    {
        parent::__construct($properties, $alexaSettingsApi);
        $this->_appointmentId = $properties['appointment_id'];
        $this->_email = $properties['email'];
        $this->_appointmentDate = $properties['appointment_date'];
        $this->_appointmentTime = $properties['appointment_time'];
        $this->_payload = $properties['payload'];
        $this->_resultVar = $properties['result_var'];
        foreach ($properties['ok'] as $element) {
            $this->_okFlow[] = $element;
            $this->addChild($element);
        }
        foreach ($properties['not_available'] as $element) {
            $this->_notAvailableFlow[] = $element;
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
        $timezone = $this->_getTimezone($request);
        $appointmentId = $this->evaluateString($this->_appointmentId);
        $email = $this->evaluateString($this->_email);
        $date = $this->evaluateString($this->_appointmentDate);
        $time = $this->evaluateString($this->_appointmentTime);
        $payload = $this->getService()->evaluateArgs($this->_payload, $this);
        $this->_logger->info('Updating appointment at [' . $date . '][' . $time . '] for customer email [' . $email . ']');
        $slot_time = new \DateTime($date . ' ' . $time, $timezone);
        $data = ['requested_timezone' => $timezone->getName(), 'requested_time' => $slot_time->getTimestamp()];
        try {
            $data['existing'] = $context->getAppointment($email, $appointmentId);
            $context->updateAppointment($email, $appointmentId, $slot_time, $payload);
            $this->_logger->info('Updated appointment successfully [' . $appointmentId . ']');
            $selected_flow = $this->_okFlow;
        } catch (\Convo\Pckg\Appointments\OutOfBusinessHoursException $e) {
            $this->_logger->info($e->getMessage());
            $selected_flow = $this->_notAvailableFlow;
            $data['not_allowed'] = \true;
        } catch (\Convo\Pckg\Appointments\SlotNotAvailableException $e) {
            $this->_logger->info($e->getMessage());
            $selected_flow = $this->_notAvailableFlow;
        }
        $params = $this->getService()->getComponentParams(IServiceParamsScope::SCOPE_TYPE_REQUEST, $this);
        $params->setServiceParam($this->_resultVar, $data);
        foreach ($selected_flow as $element) {
            $element->read($request, $response);
        }
    }
}
