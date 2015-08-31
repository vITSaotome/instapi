<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.08.2015
 * Time: 14:36
 */

namespace App\Http\Controllers;


use Psy\Util\Json;

class IndexController extends Controller {

    private $client_id = '00dd2620ca744e278e3c9f2a3652f514';

    public function index() {
        return 'index method';
    }

    private function getUserId($name){
        $base_url = 'https://api.instagram.com/v1/users/';
        $url = $base_url . 'search?q=' . $name . '&count=1&client_id='
            . $this->client_id . '&response_type=json';
        $res = $this->get_web_page($url)['content'];
        $res_arr = json_decode($res,1);
        if (is_array($res_arr['data']) && count($res_arr['data'])){
            $user_id = $res_arr['data'][0]['id'];
            $result = $user_id;
        }
        else{
            $result = 'user not found';
        }
        return $result;
    }

    public function getUserInfo($name){
        $delimiter = ',';
        if (str_contains($name, $delimiter)){
            $names_arr = explode($delimiter, $name);
        }else{
            $names_arr[] = $name;
        }
        $base_url = 'https://api.instagram.com/v1/users/';
        foreach($names_arr as $name){
            if($name != ''){
                $user_id = $this->getUserId($name);
                $url = $base_url . $user_id . '?client_id='
                    . $this->client_id . '&response_type=json';
                $result[] = json_decode($this->get_web_page($url)['content'],1);
            }
        }
        return json_encode($result);
    }

    public function getUserMedia($name, $max_id=null){
        $user_id = $this->getUserId($name);
        $base_url = 'https://api.instagram.com/v1/users/';
        $url = $base_url . $user_id . '/media/recent?client_id='
            . $this->client_id . '&response_type=json';

        if($max_id != null){
            $url .= '&max_id=' . $max_id;
        }

        $res = $this->get_web_page($url)['content'];
        $res_arr = json_decode($res, 1);

        if(is_array($res_arr['data'])&&count($res_arr['data'])){
            foreach($res_arr['data'] as $key => $value){
                unset($res_arr['data'][$key]['attribution']);
                unset($res_arr['data'][$key]['tags']);
                unset($res_arr['data'][$key]['created_time']);
                unset($res_arr['data'][$key]['location']);
                unset($res_arr['data'][$key]['filter']);
                unset($res_arr['data'][$key]['link']);
                unset($res_arr['data'][$key]['users_in_media']);
                unset($res_arr['data'][$key]['type']);
//                unset($res_arr['data'][$key]['id']);
                unset($res_arr['data'][$key]['user']);
                $res_arr['data'][$key]['comments'] = $res_arr['data'][$key]['comments']['count'];
                $res_arr['data'][$key]['likes'] = $res_arr['data'][$key]['likes']['count'];
                $res_arr['data'][$key]['url'] = $res_arr['data'][$key]['images']['standard_resolution']['url'];
                unset($res_arr['data'][$key]['images']);
            }
            dd($res_arr);
        }
    }

    public function getMediaDetails($id, $details=0){
        $delimiter = ',';
        if (str_contains($id, $delimiter)){
            $id_arr = explode($delimiter, $id);
        }else{
            $id_arr[] = $id;
        }
        $base_url = 'https://api.instagram.com/v1/media/';
        $details_error = false;
        foreach($id_arr as $media_id){
            if ($media_id != null){
                $url = $base_url . $media_id . '?client_id='
                    . $this->client_id . '&response_type=json';

                $res_t = $this->get_web_page($url)['content'];
                $res_t_arr = json_decode($res_t,1);
                $comments_count = $res_t_arr['data']['comments']['count'];
                $likes_count = $res_t_arr['data']['likes']['count'];

                if($details != null){
                    if($details === 'comments' || $details === 'stats'){
                        $url = $base_url . $media_id . '/comments?client_id='
                            . $this->client_id . '&response_type=json';
                    }elseif($details === 'likes'){
                        $url = $base_url . $media_id . '/likes?client_id='
                            . $this->client_id . '&response_type=json';
                    }else{
                        $details_error = true;
                        break;
                    }
                }
                $res = $this->get_web_page($url)['content'];
                if($details === 'comments' || $details === 'likes' || $details === 'stats'){
                    $res = substr($res,0,strpos($res, '"data"')) . '"media_id":' . '"' . $media_id . '",' . substr($res,strpos($res, '"data"'));
                }
                if($details === 'stats'){
                    $res = substr($res,0,strpos($res, '"data"')) . '"comments_count":' . $comments_count . ',' . substr($res,strpos($res, '"data"'));
                    $res = substr($res,0,strpos($res, '"data"')) . '"likes_count":' . $likes_count . ',' . substr($res,strpos($res, '"data"'));
                }
                $res_arr[] = json_decode($res,1);
            }
        }

        if($details === 'stats'){
            foreach($res_arr as $key =>$value){
                foreach($value['data'] as $k =>$val){
                    if(!isset($res_arr[$key]['day_stats'][date('Y-m-d', $val['created_time'])]['comments'])){
                        $res_arr[$key]['daily_stats'][date('Y-m-d', $val['created_time'])]['comments'] = 0;
                        $res_arr[$key]['daily_stats'][date('Y-m-d', $val['created_time'])]['likes'] = 0;
                    }
                    $res_arr[$key]['daily_stats'][date('Y-m-d', $val['created_time'])]['comments']++;
                }
                foreach($res_arr[$key]['day_stats'] as $k=>$val){
                    $res_arr[$key]['daily_stats'][$k]['likes'] = round($res_arr[$key]['likes_count'] * $res_arr[$key]['daily_stats'][$k]['comments'] / $res_arr[$key]['comments_count']);
                }
                unset($res_arr[$key]['data']);
            }
        }

//        dd($res_arr);

        if($details_error == false){
            return json_encode($res_arr);
        }else{
            return 'Bad request: [ ' . $details . ' ] is not a valid parameter.';
        }
    }

    private function get_web_page($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }
}