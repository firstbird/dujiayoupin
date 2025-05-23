<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/entity_type.proto
namespace Google\Cloud\Dialogflow\V2\EntityType;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * An **entity entry** for an associated entity type.
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.EntityType.Entity</code>
 */
class Entity extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The primary value associated with this entity entry.
     * For example, if the entity type is *vegetable*, the value could be
     * *scallions*.
     * For `KIND_MAP` entity types:
     * *   A reference value to be used in place of synonyms.
     * For `KIND_LIST` entity types:
     * *   A string that can contain references to other entity types (with or
     *     without aliases).
     *
     * Generated from protobuf field <code>string value = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $value = '';
    /**
     * Required. A collection of value synonyms. For example, if the entity type
     * is *vegetable*, and `value` is *scallions*, a synonym could be *green
     * onions*.
     * For `KIND_LIST` entity types:
     * *   This collection must contain exactly one synonym equal to `value`.
     *
     * Generated from protobuf field <code>repeated string synonyms = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $synonyms;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $value
     *           Required. The primary value associated with this entity entry.
     *           For example, if the entity type is *vegetable*, the value could be
     *           *scallions*.
     *           For `KIND_MAP` entity types:
     *           *   A reference value to be used in place of synonyms.
     *           For `KIND_LIST` entity types:
     *           *   A string that can contain references to other entity types (with or
     *               without aliases).
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $synonyms
     *           Required. A collection of value synonyms. For example, if the entity type
     *           is *vegetable*, and `value` is *scallions*, a synonym could be *green
     *           onions*.
     *           For `KIND_LIST` entity types:
     *           *   This collection must contain exactly one synonym equal to `value`.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\EntityType::initOnce();
        parent::__construct($data);
    }
    /**
     * Required. The primary value associated with this entity entry.
     * For example, if the entity type is *vegetable*, the value could be
     * *scallions*.
     * For `KIND_MAP` entity types:
     * *   A reference value to be used in place of synonyms.
     * For `KIND_LIST` entity types:
     * *   A string that can contain references to other entity types (with or
     *     without aliases).
     *
     * Generated from protobuf field <code>string value = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * Required. The primary value associated with this entity entry.
     * For example, if the entity type is *vegetable*, the value could be
     * *scallions*.
     * For `KIND_MAP` entity types:
     * *   A reference value to be used in place of synonyms.
     * For `KIND_LIST` entity types:
     * *   A string that can contain references to other entity types (with or
     *     without aliases).
     *
     * Generated from protobuf field <code>string value = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setValue($var)
    {
        GPBUtil::checkString($var, True);
        $this->value = $var;
        return $this;
    }
    /**
     * Required. A collection of value synonyms. For example, if the entity type
     * is *vegetable*, and `value` is *scallions*, a synonym could be *green
     * onions*.
     * For `KIND_LIST` entity types:
     * *   This collection must contain exactly one synonym equal to `value`.
     *
     * Generated from protobuf field <code>repeated string synonyms = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }
    /**
     * Required. A collection of value synonyms. For example, if the entity type
     * is *vegetable*, and `value` is *scallions*, a synonym could be *green
     * onions*.
     * For `KIND_LIST` entity types:
     * *   This collection must contain exactly one synonym equal to `value`.
     *
     * Generated from protobuf field <code>repeated string synonyms = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSynonyms($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->synonyms = $arr;
        return $this;
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Cloud\Dialogflow\V2\EntityType\Entity::class, \Google\Cloud\Dialogflow\V2\EntityType_Entity::class);
