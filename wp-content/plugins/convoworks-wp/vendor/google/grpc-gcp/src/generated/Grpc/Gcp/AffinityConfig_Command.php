<?php

# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: grpc_gcp.proto
namespace Convoworks\Grpc\Gcp;

/**
 * Protobuf enum <code>Grpc\Gcp\AffinityConfig\Command</code>
 */
class AffinityConfig_Command
{
    /**
     * The annotated method will be required to be bound to an existing session
     * to execute the RPC. The corresponding <affinity_key_field_path> will be
     * used to find the affinity key from the request message.
     *
     * Generated from protobuf enum <code>BOUND = 0;</code>
     */
    const BOUND = 0;
    /**
     * The annotated method will establish the channel affinity with the channel
     * which is used to execute the RPC. The corresponding
     * <affinity_key_field_path> will be used to find the affinity key from the
     * response message.
     *
     * Generated from protobuf enum <code>BIND = 1;</code>
     */
    const BIND = 1;
    /**
     * The annotated method will remove the channel affinity with the channel
     * which is used to execute the RPC. The corresponding
     * <affinity_key_field_path> will be used to find the affinity key from the
     * request message.
     *
     * Generated from protobuf enum <code>UNBIND = 2;</code>
     */
    const UNBIND = 2;
}
