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

    private function getUserId($user_name){
        $res = file_get_contents('https://api.instagram.com/v1/users/search?q=' . $user_name . '&count=1&client_id='
            . $this->client_id . '&response_type=json');
        $res_arr = json_decode($res, 1);
        if (is_array($res_arr['data']) && count($res_arr['data'])){
            $user_id = $res_arr['data'][0]['id'];
            $result = $user_id;
        }
        else{
            $result = 'user not found';
        }
        return $result;
    }

    public function getUserInfo($user_name){
        $user_id = $this->getUserId($user_name);
        $res = file_get_contents('https://api.instagram.com/v1/users/' . $user_id . '?client_id='
            . $this->client_id . '&response_type=json');
//        $res_arr = json_decode($res, 1);
        return $res;
    }

    public function getUserPhotos($user_name, $next_max_id=null){
        $user_id = $this->getUserId($user_name);
        $url = 'https://api.instagram.com/v1/users/' . $user_id . '/media/recent?client_id='
            . $this->client_id . '&response_type=json';

        if($next_max_id != null){
            $url .= '&max_id=' . $next_max_id;
        }

        $res = file_get_contents($url);
        $res_arr = json_decode($res, 1);

        if(is_array($res_arr['data'])&&count($res_arr['data'])){
            foreach($res_arr['data'] as $key => $value){
                unset($res_arr['data'][$key]['attribution']);
                unset($res_arr['data'][$key]['tags']);
                unset($res_arr['data'][$key]['created_time']);
                unset($res_arr['data'][$key]['location']);
                unset($res_arr['data'][$key]['filter']);
                unset($res_arr['data'][$key]['link']);
                unset($res_arr['data'][$key]['users_in_photo']);
                unset($res_arr['data'][$key]['type']);
//                unset($res_arr['data'][$key]['id']);
                unset($res_arr['data'][$key]['user']);
                $res_arr['data'][$key]['comments'] = $res_arr['data'][$key]['comments']['count'];
                $res_arr['data'][$key]['likes'] = $res_arr['data'][$key]['likes']['count'];
                $res_arr['data'][$key]['url'] = $res_arr['data'][$key]['images']['standard_resolution']['url'];
                unset($res_arr['data'][$key]['images']);
            }

            dd($res_arr);

//            return json_encode($res_arr);
        }

        $result = $res_arr['data'];



        dd($result);
    }

    public function getPhotoDetails($photo_id, $details){
        $base_url = 'https://api.instagram.com/v1/media/';
        $url = $base_url . $photo_id . '?client_id='
            . $this->client_id . '&response_type=json';

        if($details != null){
            if($details == 'comments'){
                $url = $base_url . $photo_id . '/comments?client_id='
                . $this->client_id . '&response_type=json';
            }elseif($details == 'likes'){
                $url = $base_url . $photo_id . '/likes?client_id='
                    . $this->client_id . '&response_type=json';
            }
        }

        $res = file_get_contents($url);
        $res_arr = json_decode($res, 1);

        dd($res_arr);
    }


//    public function getUserInfo() {
//        $user = file_get_contents('https://api.instagram.com/v1/users/search?q=damedvedev&count=1&client_id=00dd2620ca744e278e3c9f2a3652f514&response_type=json');
//        $arr = json_decode($user, 1);
////        dd($arr['data'][0]['id']);
//        $userID = $arr['data'][0]['id'];
//        $user = file_get_contents('https://api.instagram.com/v1/users/' . $userID . '/media/recent?client_id=00dd2620ca744e278e3c9f2a3652f514&response_type=json');
//        dd(json_decode($user, 1));
//    }
}