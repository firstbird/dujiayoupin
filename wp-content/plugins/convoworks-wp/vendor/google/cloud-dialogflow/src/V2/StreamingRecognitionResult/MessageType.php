<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/session.proto
namespace Google\Cloud\Dialogflow\V2\StreamingRecognitionResult;

use UnexpectedValueException;
/**
 * Type of the response message.
 *
 * Protobuf type <code>google.cloud.dialogflow.v2.StreamingRecognitionResult.MessageType</code>
 */
class MessageType
{
    /**
     * Not specified. Should never be used.
     *
     * Generated from protobuf enum <code>MESSAGE_TYPE_UNSPECIFIED = 0;</code>
     */
    const MESSAGE_TYPE_UNSPECIFIED = 0;
    /**
     * Message contains a (possibly partial) transcript.
     *
     * Generated from protobuf enum <code>TRANSCRIPT = 1;</code>
     */
    const TRANSCRIPT = 1;
    /**
     * Event indicates that the server has detected the end of the user's speech
     * utterance and expects no additional inputs.
     * Therefore, the server will not process additional audio (although it may subsequently return additional results). The
     * client should stop sending additional audio data, half-close the gRPC
     * connection, and wait for any additional results until the server closes
     * the gRPC connection. This message is only sent if `single_utterance` was
     * set to `true`, and is not used otherwise.
     *
     * Generated from protobuf enum <code>END_OF_SINGLE_UTTERANCE = 2;</code>
     */
    const END_OF_SINGLE_UTTERANCE = 2;
    private static $valueToName = [self::MESSAGE_TYPE_UNSPECIFIED => 'MESSAGE_TYPE_UNSPECIFIED', self::TRANSCRIPT => 'TRANSCRIPT', self::END_OF_SINGLE_UTTERANCE => 'END_OF_SINGLE_UTTERANCE'];
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
\class_alias(\Google\Cloud\Dialogflow\V2\StreamingRecognitionResult\MessageType::class, \Google\Cloud\Dialogflow\V2\StreamingRecognitionResult_MessageType::class);
