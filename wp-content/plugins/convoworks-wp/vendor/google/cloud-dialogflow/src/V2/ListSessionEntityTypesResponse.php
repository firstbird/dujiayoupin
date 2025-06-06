<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/session_entity_type.proto
namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * The response message for [SessionEntityTypes.ListSessionEntityTypes][google.cloud.dialogflow.v2.SessionEntityTypes.ListSessionEntityTypes].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.ListSessionEntityTypesResponse</code>
 */
class ListSessionEntityTypesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The list of session entity types. There will be a maximum number of items
     * returned based on the page_size field in the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.SessionEntityType session_entity_types = 1;</code>
     */
    private $session_entity_types;
    /**
     * Token to retrieve the next page of results, or empty if there are no
     * more results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     */
    private $next_page_token = '';
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dialogflow\V2\SessionEntityType[]|\Google\Protobuf\Internal\RepeatedField $session_entity_types
     *           The list of session entity types. There will be a maximum number of items
     *           returned based on the page_size field in the request.
     *     @type string $next_page_token
     *           Token to retrieve the next page of results, or empty if there are no
     *           more results in the list.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\SessionEntityType::initOnce();
        parent::__construct($data);
    }
    /**
     * The list of session entity types. There will be a maximum number of items
     * returned based on the page_size field in the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.SessionEntityType session_entity_types = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSessionEntityTypes()
    {
        return $this->session_entity_types;
    }
    /**
     * The list of session entity types. There will be a maximum number of items
     * returned based on the page_size field in the request.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.SessionEntityType session_entity_types = 1;</code>
     * @param \Google\Cloud\Dialogflow\V2\SessionEntityType[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSessionEntityTypes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dialogflow\V2\SessionEntityType::class);
        $this->session_entity_types = $arr;
        return $this;
    }
    /**
     * Token to retrieve the next page of results, or empty if there are no
     * more results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @return string
     */
    public function getNextPageToken()
    {
        return $this->next_page_token;
    }
    /**
     * Token to retrieve the next page of results, or empty if there are no
     * more results in the list.
     *
     * Generated from protobuf field <code>string next_page_token = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setNextPageToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_page_token = $var;
        return $this;
    }
}
