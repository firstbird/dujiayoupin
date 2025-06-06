<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/intent.proto
namespace Google\Cloud\Dialogflow\V2\Intent\Message\BrowseCarouselCard;

use UnexpectedValueException;
/**
 * Image display options for Actions on Google. This should be used for
 * when the image's aspect ratio does not match the image container's
 * aspect ratio.
 *
 * Protobuf type <code>google.cloud.dialogflow.v2.Intent.Message.BrowseCarouselCard.ImageDisplayOptions</code>
 */
class ImageDisplayOptions
{
    /**
     * Fill the gaps between the image and the image container with gray
     * bars.
     *
     * Generated from protobuf enum <code>IMAGE_DISPLAY_OPTIONS_UNSPECIFIED = 0;</code>
     */
    const IMAGE_DISPLAY_OPTIONS_UNSPECIFIED = 0;
    /**
     * Fill the gaps between the image and the image container with gray
     * bars.
     *
     * Generated from protobuf enum <code>GRAY = 1;</code>
     */
    const GRAY = 1;
    /**
     * Fill the gaps between the image and the image container with white
     * bars.
     *
     * Generated from protobuf enum <code>WHITE = 2;</code>
     */
    const WHITE = 2;
    /**
     * Image is scaled such that the image width and height match or exceed
     * the container dimensions. This may crop the top and bottom of the
     * image if the scaled image height is greater than the container
     * height, or crop the left and right of the image if the scaled image
     * width is greater than the container width. This is similar to "Zoom
     * Mode" on a widescreen TV when playing a 4:3 video.
     *
     * Generated from protobuf enum <code>CROPPED = 3;</code>
     */
    const CROPPED = 3;
    /**
     * Pad the gaps between image and image frame with a blurred copy of the
     * same image.
     *
     * Generated from protobuf enum <code>BLURRED_BACKGROUND = 4;</code>
     */
    const BLURRED_BACKGROUND = 4;
    private static $valueToName = [self::IMAGE_DISPLAY_OPTIONS_UNSPECIFIED => 'IMAGE_DISPLAY_OPTIONS_UNSPECIFIED', self::GRAY => 'GRAY', self::WHITE => 'WHITE', self::CROPPED => 'CROPPED', self::BLURRED_BACKGROUND => 'BLURRED_BACKGROUND'];
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
\class_alias(\Google\Cloud\Dialogflow\V2\Intent\Message\BrowseCarouselCard\ImageDisplayOptions::class, \Google\Cloud\Dialogflow\V2\Intent_Message_BrowseCarouselCard_ImageDisplayOptions::class);
