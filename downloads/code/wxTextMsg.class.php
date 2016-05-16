<?php

/**
 * Created by PhpStorm.
 * User: lzx
 * Date: 2016/5/5
 * Time: 16:26
 */

require_once 'wxMsg.class.php';

class wxTextMsg
{

    function reply($postObj)
    {

        $wxMsgObj = new wxMsg();
        $keyword = trim($postObj->Content); //消息内容

        switch ($keyword) {
            case 'hehe' :
                $contentStr = '呵你个鬼啊！';
                return $wxMsgObj -> respText($postObj, $contentStr);
            case (preg_match('/.*天气$/', $keyword) ? true : false):
                $contentStr = $this -> queryWeather($keyword);
                return $wxMsgObj->respText($postObj, $contentStr);
                break;
            case (preg_match('/\d{11}/',$keyword) ? true : false):
                $contentStr = $this -> queryTelephone($keyword);
                return $wxMsgObj -> respText($postObj, $contentStr);
                break;
            default :
                $contentStr = <<<eof
可尝试输入如下内容：
1、测试：hehe
2、查天气：xx天气，如武汉天气
3、查手机归属地：15602862685，若有多个号码，可使用逗号或空格隔开
eof;
                ;
                return $wxMsgObj->respText($postObj, $contentStr);
                break;
        }
    }

    function queryWeather($keyword){
        $city = trim(str_replace(array('天气','市'), "", $keyword));
        $ch = curl_init();  //使用CURL操作，详细可查看手册及Google
        $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityname=' . $city;
        $header = array('apikey: '.BAIDU_API_KEY); //此处的apikey使用自己申请的apikey，已通过常量定义

        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  //设置HTTP头部
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  //以字符串形式返回
        // 执行HTTP请求
        curl_setopt($ch, CURLOPT_URL, $url);
        $res = curl_exec($ch);  //执行URL操作，调用API
        $res = json_decode($res, true);  //解析JSON数据，并返回关联数组
        $contentStr = '';
        foreach ($res['retData'] as $k => $v ){
            if($k == 'city'){
                $contentStr .= '城市：'.$v."\n";
            }
            elseif($k == 'today'){
                $contentStr .= "今天：".$v['date']."\n";
                $contentStr .= "天气：".$v['type']."\n";
                $contentStr .= "风力：".$v['fengli']."\n";
                $contentStr .= "空气质量指数：".$v['aqi']."\n";
                $contentStr .= "当前温度：".$v['curTemp']."\n";
                $contentStr .= "最高温度：".$v['hightemp']."\n";
                $contentStr .= "最低温度：".$v['lowtemp']."\n";
//                如果下面的也显示，则信息太多
//                $contentStr .= "感冒指数：".$v['index'][0]['details']."\n";
//                $contentStr .= "防晒指数：".$v['index'][1]['details']."\n";
//                $contentStr .= "穿衣指数：".$v['index'][2]['details']."\n";
//                $contentStr .= "运动指数：".$v['index'][3]['details']."\n";
//                $contentStr .= "洗车指数：".$v['index'][4]['details']."\n";
//                $contentStr .= "晾晒指数：".$v['index'][5]['details']."\n";
            }
            elseif($k == 'forecast'){
                $contentStr .= "\n";
                $contentStr .= "明天：".$v[0]['date']."\n";
                $contentStr .= "天气：".$v[0]['type']."\n";
                $contentStr .= "最高温度：".$v[0]['hightemp']."\n";
                $contentStr .= "最低温度：".$v[0]['lowtemp']."\n";

                $contentStr .= "\n";
                $contentStr .= "后天：".$v[1]['date']."\n";
                $contentStr .= "天气：".$v[1]['type']."\n";
                $contentStr .= "最高温度：".$v[1]['hightemp']."\n";
                $contentStr .= "最低温度：".$v[1]['lowtemp']."";
            }
        }

        return $contentStr;

    }

    function queryTelephone($keyword){
        $keyword = trim($keyword);
        $delimiter = array('，',' ');
        $keyword = str_replace($delimiter , ',' , $keyword);
        $ch = curl_init();
        $url = 'http://apis.baidu.com/baidu_mobile_security/phone_number_service/phone_information_query?tel='.$keyword.'&location=true';
        $header = array(
            'apikey: '.BAIDU_API_KEY,
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $res = json_decode($res, true);  //解析JSON数据，并返回关联数组

        $tel = explode(',',$keyword);
        $contentStr = "";


//        foreach($tel as $v){
//            foreach ($res['response']["$v"] as $key=>$value){
//                if($key == 'location') {
//                    $contentStr .= $v .' ' . $value['province'] . $value['operators'] . ' ' . $value['city'] . "\n";
//                }
//            }
//        }

//        上面这样写太脑残了，白绕一圈，改成下面这样的
        foreach ($tel as $v){
            $value = $res['response']["$v"]['location'];
            $contentStr .= $v .' ' . $value['province'] . $value['operators'] . ' ' . $value['city'] . "\n";
        }

        return $contentStr;
    }
}