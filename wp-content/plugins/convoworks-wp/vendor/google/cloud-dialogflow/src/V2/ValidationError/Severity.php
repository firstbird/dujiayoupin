<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/validation_result.proto
namespace Google\Cloud\Dialogflow\V2\ValidationError;

use UnexpectedValueException;
/**
 * Represents a level of severity.
 *
 * Protobuf type <code>google.cloud.dialogflow.v2.ValidationError.Severity</code>
 */
class Severity
{
    /**
     * Not specified. This value should never be used.
     *
     * Generated from protobuf enum <code>SEVERITY_UNSPECIFIED = 0;</code>
     */
    const SEVERITY_UNSPECIFIED = 0;
    /**
     * The agent doesn't follow Dialogflow best practicies.
     *
     * Generated from protobuf enum <code>INFO = 1;</code>
     */
    const INFO = 1;
    /**
     * The agent may not behave as expected.
     *
     * Generated from protobuf enum <code>WARNING = 2;</code>
     */
    const WARNING = 2;
    /**
     * The agent may experience partial failures.
     *
     * Generated from protobuf enum <code>ERROR = 3;</code>
     */
    const ERROR = 3;
    /**
     * The agent may completely fail.
     *
     * Generated from protobuf enum <code>CRITICAL = 4;</code>
     */
    const CRITICAL = 4;
    private static $valueToName = [self::SEVERITY_UNSPECIFIED => 'SEVERITY_UNSPECIFIED', self::INFO => 'INFO', self::WARNING => 'WARNING', self::ERROR => 'ERROR', self::CRITICAL => 'CRITICAL'];
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
\class_alias(\Google\Cloud\Dialogflow\V2\ValidationError\Severity::class, \Google\Cloud\Dialogflow\V2\ValidationError_Severity::class);
