<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/dialogflow/v2/session.proto
namespace Google\Cloud\Dialogflow\V2;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;
/**
 * Configures the types of sentiment analysis to perform.
 *
 * Generated from protobuf message <code>google.cloud.dialogflow.v2.SentimentAnalysisRequestConfig</code>
 */
class SentimentAnalysisRequestConfig extends \Google\Protobuf\Internal\Message
{
    /**
     * Instructs the service to perform sentiment analysis on
     * `query_text`. If not provided, sentiment analysis is not performed on
     * `query_text`.
     *
     * Generated from protobuf field <code>bool analyze_query_text_sentiment = 1;</code>
     */
    private $analyze_query_text_sentiment = \false;
    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type bool $analyze_query_text_sentiment
     *           Instructs the service to perform sentiment analysis on
     *           `query_text`. If not provided, sentiment analysis is not performed on
     *           `query_text`.
     * }
     */
    public function __construct($data = NULL)
    {
        \GPBMetadata\Google\Cloud\Dialogflow\V2\Session::initOnce();
        parent::__construct($data);
    }
    /**
     * Instructs the service to perform sentiment analysis on
     * `query_text`. If not provided, sentiment analysis is not performed on
     * `query_text`.
     *
     * Generated from protobuf field <code>bool analyze_query_text_sentiment = 1;</code>
     * @return bool
     */
    public function getAnalyzeQueryTextSentiment()
    {
        return $this->analyze_query_text_sentiment;
    }
    /**
     * Instructs the service to perform sentiment analysis on
     * `query_text`. If not provided, sentiment analysis is not performed on
     * `query_text`.
     *
     * Generated from protobuf field <code>bool analyze_query_text_sentiment = 1;</code>
     * @param bool $var
     * @return $this
     */
    public function setAnalyzeQueryTextSentiment($var)
    {
        GPBUtil::checkBool($var);
        $this->analyze_query_text_sentiment = $var;
        return $this;
    }
}
