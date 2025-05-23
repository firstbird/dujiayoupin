<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/intent.proto
namespace Google\Cloud\Dialogflow\V2\Intent\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * The card for presenting a carousel of options to select from.
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.Intent.Message.CarouselSelect</code>
 */
class CarouselSelect extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Carousel items.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.Intent.Message.CarouselSelect.Item items = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $items;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\Dialogflow\V2\Intent\Message\CarouselSelect\Item[]|\Google\Protobuf\Internal\RepeatedField $items
     *           Required. Carousel items.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Intent::initOnce();
        parent::__construct($data);
    }
    /**
     * Required. Carousel items.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.Intent.Message.CarouselSelect.Item items = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getItems()
    {
        return $this->items;
    }
    /**
     * Required. Carousel items.
     *
     * Generated from protobuf field <code>repeated .google.cloud.dialogflow.v2.Intent.Message.CarouselSelect.Item items = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Dialogflow\V2\Intent\Message\CarouselSelect\Item[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setItems($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Dialogflow\V2\Intent\Message\CarouselSelect\Item::class);
        $this->items = $arr;
        return $this;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Cloud\Dialogflow\V2\Intent\Message\CarouselSelect::class, \Google\Cloud\Dialogflow\V2\Intent_Message_CarouselSelect::class);
