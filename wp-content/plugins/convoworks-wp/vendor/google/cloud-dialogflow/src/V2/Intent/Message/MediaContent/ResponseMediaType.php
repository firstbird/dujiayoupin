<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/intent.proto
namespace Google\Cloud\Dialogflow\V2\Intent\Message\MediaContent;

use UnexpectedValueException;
/**
 * Format of response media type.
 *
 * Protobuf type <code>google.cloud.dialogflow.v2.Intent.Message.MediaContent.ResponseMediaType</code>
 */
class ResponseMediaType
{
    /**
     * Unspecified.
     *
     * Generated from protobuf enum <code>RESPONSE_MEDIA_TYPE_UNSPECIFIED = 0;</code>
     */
    const RESPONSE_MEDIA_TYPE_UNSPECIFIED = 0;
    /**
     * Response media type is audio.
     *
     * Generated from protobuf enum <code>AUDIO = 1;</code>
     */
    const AUDIO = 1;
    private static $valueToName = [self::RESPONSE_MEDIA_TYPE_UNSPECIFIED => 'RESPONSE_MEDIA_TYPE_UNSPECIFIED', self::AUDIO => 'AUDIO'];
    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(\sprintf('Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }
    public static function value($name)
    {
        $const = __CLASS__ . '::' . \strtoupper($name);
        if (!\defined($const)) {
            throw new UnexpectedValueException(\sprintf('Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return \constant($const);
    }
}
// Adding a class alias for backwards compatibility with the previous class name.
\class_alias(\Google\Cloud\Dialogflow\V2\Intent\Message\MediaContent\ResponseMediaType::class, \Google\Cloud\Dialogflow\V2\Intent_Message_MediaContent_ResponseMediaType::class);
