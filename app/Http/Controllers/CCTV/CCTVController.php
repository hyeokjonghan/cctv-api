<?php

namespace App\Http\Controllers\CCTV;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Lib\CURLController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CCTVController extends Controller
{

    // 카메라 목록 호출 (테스트 완료, 추후 데이터 수정만 해주면 됨)
    public function camera(Request $request) {
        $uri = "https://int.tbapi.kt.com/gigaeyes/v1.0/camera";
        $authToken = $request->user()['giga_auth_token'];
        $header = [
            'Content-Type:application/json;charset=UTF-8',
            'Authorization:Basic '.base64_encode(env('GIGA_ID').':'.env('GIGA_PASS')),
            'User-Agent:GiGAeyes (compatible;DeviceType/iPhone;DeviceModel/SCH-M20;DeviceId/3F2A009CDE;OSType/iOS;OSVersion/5.1.1;AppVersion/3.0.0;IpAddr/0.0.0.1)',
            'authToken:'.$authToken
        ];
        $body = [
            'request'=>[
                'account_id'=>'0000843682'  // Test Data
            ]
        ];
        $body = json_encode($body, true);

        $curlController = new CURLController();
        $returnData = $curlController->postCURL($uri, $body, $header);
        $returnData = json_decode($returnData['data'], true);
        return $returnData;
    }

    // 레코드 비디오 목록 (테스트 대기 중)
    public function recodeVideo(Request $request) {
        $uri = "https://int.tbapi.kt.com/gigaeyes/v1.0/recordVideo";
        $authToken = $request->user()['giga_auth_token'];
        $header = [
            'Content-Type:application/json;charset=UTF-8',
            'Authorization:Basic '.base64_encode(env('GIGA_ID').':'.env('GIGA_PASS')),
            'User-Agent:GiGAeyes (compatible;DeviceType/iPhone;DeviceModel/SCH-M20;DeviceId/3F2A009CDE;OSType/iOS;OSVersion/5.1.1;AppVersion/3.0.0;IpAddr/0.0.0.1)',
            'authToken:'.$authToken
        ];

        $body = [
            'request'=>[
                'cam_id'=>'D00008436821019',
                'start_time'=>'20230307101000',
                'end_time'=>'20230307101020'
            ]
        ];

        //yyyymmddhhmmss

        $body = json_encode($body, true);

        $curlController = new CURLController();
        $returnData = $curlController->postCURL($uri, $body, $header);
        $returnData = json_decode($returnData['data'], true);
        return $returnData;
    }

    public function recordVideoList(Request $request) {
        $uri = "https://int.tbapi.kt.com/gigaeyes/v1.0/recordVideoList";
        $authToken = $request->user()['giga_auth_token'];
        $header = [
            'Content-Type:application/json;charset=UTF-8',
            'Authorization:Basic '.base64_encode(env('GIGA_ID').':'.env('GIGA_PASS')),
            'User-Agent:GiGAeyes (compatible;DeviceType/iPhone;DeviceModel/SCH-M20;DeviceId/3F2A009CDE;OSType/iOS;OSVersion/5.1.1;AppVersion/3.0.0;IpAddr/0.0.0.1)',
            'authToken:'.$authToken
        ];

        $body = [
            'request'=>[
                'cam_ids'=>['D00008436821019'],
                'start_time'=>'20230307101000',
                'end_time'=>'20230307101020'
            ]
        ];

        //yyyymmddhhmmss

        $body = json_encode($body, true);

        $curlController = new CURLController();
        $returnData = $curlController->postCURL($uri, $body, $header);
        $returnData = json_decode($returnData['data'], true);
        return $returnData;
    }

    // TEST Account API ==> OK
    public function accountInfo(Request $request) {
        $uri = "https://int.tbapi.kt.com/gigaeyes/v1.0/account";
        $authToken = $request->user()['giga_auth_token'];
        $header = [
            'Content-Type:application/json;charset=UTF-8',
            'Authorization:Basic '.base64_encode(env('GIGA_ID').':'.env('GIGA_PASS')),
            'User-Agent:GiGAeyes (compatible;DeviceType/iPhone;DeviceModel/SCH-M20;DeviceId/3F2A009CDE;OSType/iOS;OSVersion/5.1.1;AppVersion/3.0.0;IpAddr/0.0.0.1)',
            'authToken:'.$authToken
        ];
        $body = [
            'request'=>[]
        ];

        $body = json_encode($body, true);
        
        $curlController = new CURLController();
        $returnData = $curlController->postCURL($uri, $body, $header);
        $returnData = json_decode($returnData['data'], true);
        return $returnData;
    }

    // AuthToken 갱신
    public function getAuthToken(Request $request)
    {
        
        // $uri = "https://openapis.kt.com/gigaeyes/v1.0/authToken";
        $uri = "https://int.tbapi.kt.com/gigaeyes/v1.0/authToken";
        $header = [
            'Content-Type:application/json;charset=UTF-8',
            'Authorization:Basic '.base64_encode(env('GIGA_ID').':'.env('GIGA_PASS'))
        ];


        $body = [
            'request'=>[
                'auth_ticket'=>$this->generateAuthTicket(),
                'offset_position'=>700,
                'offset_length'=>128,
                'site_id'=>'OPENAPI246'
            ]
        ];

        $body = json_encode($body,true);

        $curlController = new CURLController();
        $returnData = $curlController->postCURL($uri,$body,$header);
        
        $returnData = json_decode($returnData['data'],true);
        if($returnData['returndescription'] == "Success") {
            $authToken = $returnData['response']['auth_token'];
            User::where('id', $request->user()['id'])->update(['giga_auth_token'=>$authToken]);
            return $authToken;
        } else {
            return response()->caps('auth token generate error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    // AuthTicket 갱신
    public function generateAuthTicket() {
        $result = shell_exec('python '.app_path().'/AuthTicket/CertGenerator.py');
        return $result;
    }
}