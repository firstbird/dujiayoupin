<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/agent.proto
namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * The request message for [Agents.SetAgent][google.cloud.dialogflow.v2.Agents.SetAgent].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.SetAgentRequest</code>
 */
class SetAgentRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The agent to update.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Agent agent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $agent = null;
    /**
     * Optional. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $update_mask = null;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dialogflow\V2\Agent $agent
     *           Required. The agent to update.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           Optional. The mask to control which fields get updated.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Agent::initOnce();
        parent::__construct($data);
    }
    /**
     * Required. The agent to update.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Agent agent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Dialogflow\V2\Agent
     */
    public function getAgent()
    {
        return isset($this->agent) ? $this->agent : null;
    }
    public function hasAgent()
    {
        return isset($this->agent);
    }
    public function clearAgent()
    {
        unset($this->agent);
    }
    /**
     * Required. The agent to update.
     *
     * Generated from protobuf field <code>.google.cloud.dialogflow.v2.Agent agent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Dialogflow\V2\Agent $var
     * @return $this
     */
    public function setAgent($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Dialogflow\V2\Agent::class);
        $this->agent = $var;
        return $this;
    }
    /**
     * Optional. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Protobuf\FieldMask
     */
    public function getUpdateMask()
    {
        return isset($this->update_mask) ? $this->update_mask : null;
    }
    public function hasUpdateMask()
    {
        return isset($this->update_mask);
    }
    public function clearUpdateMask()
    {
        unset($this->update_mask);
    }
    /**
     * Optional. The mask to control which fields get updated.
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Protobuf\FieldMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FieldMask::class);
        $this->update_mask = $var;
        return $this;
    }
}
