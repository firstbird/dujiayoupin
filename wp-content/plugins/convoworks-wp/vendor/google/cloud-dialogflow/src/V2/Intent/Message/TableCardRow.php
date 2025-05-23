<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/intent.proto
namespace Google\Cloud\Dialogflow\V2\Intent\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Row of [TableCard][google.cloud.dialogflow.v2.Intent.Message.TableCard].
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.Intent.Message.TableCardRow</code>
 */
class TableCardRow extends \Google\Protobuf\Internal\Message
{
    /**
     * Optional. List of cells that make up this row.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.Intent.Message.TableCardCell cells = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $cells;
    /**
     * Optional. Whether to add a visual divider after this row.
     *
     * Generated from protobuf field <code>bool divider_after = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $divider_after = \false;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dialogflow\V2\Intent\Message\TableCardCell[]|\Google\Protobuf\Internal\RepeatedField $cells
     *           Optional. List of cells that make up this row.
     *     @type bool $divider_after
     *           Optional. Whether to add a visual divider after this row.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Intent::initOnce();
        parent::__construct($data);
    }
    /**
     * Optional. List of cells that make up this row.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.Intent.Message.TableCardCell cells = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getCells()
    {
        return $this->cells;
    }
    /**
     * Optional. List of cells that make up this row.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.Intent.Message.TableCardCell cells = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Cloud\Dialogflow\V2\Intent\Message\TableCardCell[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setCells($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dialogflow\V2\Intent\Message\TableCardCell::class);
        $this->cells = $arr;
        return $this;
    }
    /**
     * Optional. Whether to add a visual divider after this row.
     *
     * Generated from protobuf field <code>bool divider_after = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return bool
     */
    public function getDividerAfter()
    {
        return $this->divider_after;
    }
    /**
     * Optional. Whether to add a visual divider after this row.
     *
     * Generated from protobuf field <code>bool divider_after = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param bool $var
     * @return $this
     */
    public function setDividerAfter($var)
    {
        GPBUtil::checkBool($var);
        $this->divider_after = $var;
        return $this;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Cloud\Dialogflow\V2\Intent\Message\TableCardRow::class, \Google\Cloud\Dialogflow\V2\Intent_Message_TableCardRow::class);
