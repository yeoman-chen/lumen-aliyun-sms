<?php

namespace Yeoman\LaravelAliyunSms;

use Yeoman\AliyunCore\Profile\DefaultProfile;
use Yeoman\AliyunCore\DefaultAcsClient;
use Yeoman\AliyunCore\Regions\Endpoint;
use Yeoman\AliyunCore\Regions\EndpointConfig;
use Yeoman\AliyunCore\Regions\EndpointProvider;
use Yeoman\AliyunSms\Sms\Request\V20160927\SingleSendSmsRequest;
use Yeoman\AliyunCore\Exception\ClientException;
use Yeoman\AliyunCore\Exception\ServerException;

class AliyunSms {

    public function send($mobile, $tplId, $params)
    {
        defined('ENABLE_HTTP_PROXY') or define('ENABLE_HTTP_PROXY', env('ALIYUN_SMS_ENABLE_HTTP_PROXY', false));
        defined('HTTP_PROXY_IP') or define('HTTP_PROXY_IP',     env('ALIYUN_SMS_HTTP_PROXY_IP', '127.0.0.1'));
        defined('HTTP_PROXY_PORT') or define('HTTP_PROXY_PORT',   env('ALIYUN_SMS_HTTP_PROXY_PORT', '8888'));


        $endpoint = new Endpoint('cn-hangzhou', EndpointConfig::getregionIds(), EndpointConfig::getProducDomains());
        $endpoints = array($endpoint);
        EndpointProvider::setEndpoints($endpoints);

        $iClientProfile = DefaultProfile::getProfile('cn-hangzhou', ENV('ALIYUN_ACCESS_KEY'), ENV('ALIYUN_ACCESS_SECRET'));
        $client = new DefaultAcsClient($iClientProfile);
        $request = new SingleSendSmsRequest();
        $request->setSignName(ENV('ALIYUN_SMS_SIGN_NAME')); /*签名名称*/
        $request->setTemplateCode($tplId);                /*模板code*/
        $request->setRecNum($mobile);                     /*目标手机号*/
        $request->setParamString(json_encode($params));/*模板变量，数字一定要转换为字符串*/

        try {
            $response = $client->getAcsResponse($request);
            return $response;
        } catch (ClientException  $e) {
            app('sms.log')->error('客户端错误');
            app('sms.log')->error('ErrorCode : '.$e->getErrorCode());
            app('sms.log')->error('ErrorMessage : '.$e->getErrorMessage());
        } catch (ServerException  $e) {
            app('sms.log')->error('服务端错误');
            app('sms.log')->error($e->getErrorCode());
            app('sms.log')->error($e->getErrorMessage());
        }

        return false;
    }

}