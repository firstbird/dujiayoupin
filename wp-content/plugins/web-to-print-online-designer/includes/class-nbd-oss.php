<?php
/**
 * 阿里云OSS REST API分页获取文件
 */

if (!defined('ABSPATH')) {
    exit;
}

// 引入OSS SDK
require_once 'aliyun-oss-php-sdk-master/autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Credentials\EnvironmentVariableCredentialsProvider;

// 添加初始化检查
error_log('NBD_OSS: 类文件已加载');

class NBD_OSS {
    private $accessKeyId;
    private $accessKeySecret;
    private $bucket;
    private $region;
    private $endpoint;
    private $host;
    private $ossClient;

    public function __construct($config) {
        error_log('NBD_OSS: 构造函数被调用，配置信息：' . json_encode($config));
        $this->accessKeyId = $config['accessKeyId'];
        $this->accessKeySecret = $config['accessKeySecret'];
        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->endpoint = "https://{$this->region}";
        $this->host = "https://{$this->bucket}.{$this->region}";

        try {
            $this->ossClient = new OssClient(
                $this->accessKeyId,
                $this->accessKeySecret,
                $this->endpoint
            );
        } catch (OssException $e) {
            error_log("初始化OSS客户端失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function listBucketFiles() {
        try {
            $listObjectInfo = $this->ossClient->listObjects($this->bucket);
            $objectList = $listObjectInfo->getObjectList();
            $prefixList = $listObjectInfo->getPrefixList();
            
            $result = [
                'objects' => [],
                'prefixes' => []
            ];

            if (!empty($objectList)) {
                foreach ($objectList as $objectInfo) {
                    // 获取对象的签名URL，默认有效期3600秒（1小时）
                    $signedUrl = $this->ossClient->signUrl($this->bucket, $objectInfo, 3600);
                    
                    $result['objects'][] = [
                        'key' => $objectInfo->getKey(),
                        'size' => $objectInfo->getSize(),
                        'type' => $objectInfo->getType(),
                        'lastModified' => $objectInfo->getLastModified(),
                        'url' => $signedUrl
                    ];
                }
            }

            if (!empty($prefixList)) {
                foreach ($prefixList as $prefixInfo) {
                    $result['prefixes'][] = [
                        'prefix' => $prefixInfo->getPrefix()
                    ];
                }
            }

            return $result;

        } catch (OssException $e) {
            error_log("获取文件列表失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function getBucketInfo() {
        try {
            $info = $this->ossClient->getBucketInfo($this->bucket);    
            error_log("bucket name:" . $info->getName());    
            error_log("bucket location:" . $info->getLocation());    
            error_log("bucket creation time:" . $info->getCreateDate());    
            error_log("bucket storage class:" . $info->getStorageClass());   
            error_log("bucket extranet endpoint:" . $info->getExtranetEndpoint());    
            error_log("bucket intranet endpoint:" . $info->getIntranetEndpoint());
            return $info;
        } catch (OssException $e) {
            error_log(__FUNCTION__ . ": FAILED");
            error_log($e->getMessage() . "\n");
            throw $e;
        }  
    }

    /**
     * 分页获取文件列表
     */
    public function listObjects($prefix = '', $marker = '', $maxKeys = 10) {
        try {
            $options = array(
                'prefix' => $prefix,
                'max-keys' => $maxKeys,
                'marker' => $marker
            );
            
            $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
            
            $files = [];
            foreach ($listObjectInfo->getObjectList() as $objectInfo) {
                $files[] = [
                    'key' => $objectInfo->getKey(),
                    'size' => $objectInfo->getSize(),
                    'lastModified' => $objectInfo->getLastModified(),
                    'url' => $this->getObjectUrl($objectInfo->getKey())
                ];
            }

            return [
                'files' => $files,
                'nextContinuationToken' => $listObjectInfo->getNextMarker(),
                'isTruncated' => $listObjectInfo->getIsTruncated()
            ];

        } catch (OssException $e) {
            error_log("获取文件列表失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取所有文件
     */
    public function listAllFiles($prefix = '') {
        $marker = '';
        $allFiles = [];
        
        do {
            $result = $this->listObjects($prefix, $marker);
            $allFiles = array_merge($allFiles, $result['files']);
            $marker = $result['nextContinuationToken'];
        } while ($marker);

        return $allFiles;
    }

    /**
     * 获取单个对象的URL
     */
    public function getObjectUrl($objectKey, $expireTime = 3600) {
        try {
            // $options = array(
            //     OssClient::OSS_HEADERS => array(
            //         OssClient::OSS_TRAFFIC_LIMIT => 819200,
            //     ));
            return $this->host . '/' . $objectKey;
            // return $this->ossClient->signUrl($this->bucket, $objectKey, $expireTime);
        } catch (OssException $e) {
            error_log("获取对象URL失败: " . $e->getMessage());
            throw $e;
        }
    }
}