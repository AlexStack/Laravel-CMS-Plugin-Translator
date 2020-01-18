<?php

namespace Amila\LaravelCms\Plugins\Translator\Controllers;

use AlexStack\LaravelCms\Helpers\LaravelCmsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslatorController extends Controller
{
    private $user = null;
    private $helper;

    private $app_id = null;
    private $app_key = null;

    public function __construct()
    {
        $this->helper = new LaravelCmsHelper();
    }

    public function checkUser()
    {
        // return true;
        if (! $this->user) {
            $this->user = $this->helper->hasPermission();
        }
    }

    public function create($form_data, $page, $plugin_settings)
    {
        // set a default select box value to the form
        // any better idea than change the $_GET?
        $_GET['translate_to'] = $_COOKIE['translate_to'] ?? $this->helper->s('template.frontend_language');
        $data['translate_from'] = $_COOKIE['translate_from'] ?? 'en';

        $_GET['append_source_content'] = $_COOKIE['append_source_content'] ?? 'yes';

        $_GET['translate_result_add_to_field'] = $_COOKIE['translate_result_add_to_field'] ?? 'main_content';

        if ($plugin_settings['api_provider'] == 'baidu') {
            $data['translate_languages'] = ["en"=>"English", "zh"=>"Chinese", "epa"=>"Spanish", "ara"=>"Arabic", "jp"=>"Japanese", "hi"=>"Hindi", "pt"=>"Portuguese", "fra"=>"French", "ru"=>"Russian", "de"=>"German", "kor"=>"Korean", "it"=>"Italian", "th"=>"Thai"];
        } else {
            $data['translate_languages'] = ["en"=>"English", "zh"=>"Chinese", "es"=>"Spanish", "ar"=>"Arabic", "ja"=>"Japanese", "hi"=>"Hindi", "pt"=>"Portuguese", "fr"=>"French", "ru"=>"Russian", "de"=>"German", "ko"=>"Korean", "it"=>"Italian", "la"=>"Latin"];
        }
        return $data;
    }

    public function edit($id, $page, $plugin_settings)
    {
        $page->translate_to = $_COOKIE['translate_to'] ?? $this->helper->s('template.frontend_language');

        $page->translate_from = $_COOKIE['translate_from'] ?? 'en';

        $page->append_source_content = $_COOKIE['append_source_content'] ?? 'yes';

        $page->translate_result_add_to_field = $_COOKIE['translate_result_add_to_field'] ?? 'main_content';

        if ($plugin_settings['api_provider'] == 'baidu') {
            $data['translate_languages'] = ["en"=>"English", "zh"=>"Chinese", "epa"=>"Spanish", "ara"=>"Arabic", "jp"=>"Japanese", "hi"=>"Hindi", "pt"=>"Portuguese", "fra"=>"French", "ru"=>"Russian", "de"=>"German", "kor"=>"Korean", "it"=>"Italian", "th"=>"Thai"];
        } else {
            $data['translate_languages'] = ["en"=>"English", "zh"=>"Chinese", "es"=>"Spanish", "ar"=>"Arabic", "ja"=>"Japanese", "hi"=>"Hindi", "pt"=>"Portuguese", "fr"=>"French", "ru"=>"Russian", "de"=>"German", "ko"=>"Korean", "it"=>"Italian", "la"=>"Latin"];
        }
        return $data;
    }

    public function store($form_data, $page, $plugin_settings)
    {
        return $this->update($form_data, $page, $plugin_settings);
    }

    public function update($form_data, $page, $plugin_settings)
    {
        if (trim($form_data['translate_content']) == '') {
            return false;
        }
        if ($plugin_settings['api_provider'] == 'baidu') {
            $this->app_id = $plugin_settings['app_id'];
            $this->app_key = $plugin_settings['app_key'];

            $api_result = $this->baiduTranslate($form_data['translate_content'], $form_data['translate_from'], $form_data['translate_to']);
            if (isset($api_result['trans_result'][0]['dst'])) {
                $translate_result = '<div class="translate-content">';
                foreach ($api_result['trans_result'] as $rs) {
                    if ($form_data['append_source_content'] == 'yes') {
                        $translate_result .= '<div class="src">' . $rs['src'] . '</div>';
                    }
                    $translate_result .= '' . $rs['dst'] . '<br/><br/>';
                }
                $translate_result .= '</div>';
                $new_content = $page[$form_data['translate_result_add_to_field']] . $translate_result;
            } else {
                if (request()->debug) {
                    $this->helper->debug($api_result);
                }
                return false;
            }
        }


        // $this->helper->debug($api_result);
        if (isset($new_content)) {
            $page->update([$form_data['translate_result_add_to_field']=>$new_content]);
        }

        // set cookie
        $expire_time = time() + 3600 * 24 * 180; // 180 days
        setcookie('translate_to', $form_data['translate_to'], $expire_time, '/');
        setcookie('translate_from', $form_data['translate_from'], $expire_time, '/');
        setcookie('append_source_content', $form_data['append_source_content'], $expire_time, '/');
        setcookie('translate_result_add_to_field', $form_data['translate_result_add_to_field'], $expire_time, '/');
    }

    // public function destroy(Request $request, $id)
    // {
    //     //
    // }

    /*
     * Other methods.
     */


    // baidu FanYi translate
    public function baiduTranslate($query, $from, $to)
    {
        $app_id  = $this->app_id;
        $app_key = $this->app_key;
        if (strlen($app_id) < 10 || strlen($app_key) < 10) {
            exit("Please edit the app_id & app_key in the translator plugin setting page, you can get them from https://api.fanyi.baidu.com/");
        }

        $args = [
            'q'     => $query,
            'appid' => $app_id,
            'salt'  => rand(10000, 99999),
            'from'  => $from,
            'to'    => $to,
        ];
        $args['sign'] = $this->buildSign($query, $args['appid'], $args['salt'], $app_key);
        $ret          = $this->baiduCall('http://api.fanyi.baidu.com/api/trans/vip/translate', $args);
        $ret          = json_decode($ret, true);

        return $ret;
    }

    //加密
    public function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID.$query.$salt.$secKey;
        $ret = md5($str);

        return $ret;
    }



    //发起网络请求
    public function baiduCall($url, $args=null, $method='post', $testflag = 0, $timeout = 10, $headers=[])
    {
        $ret = false;
        $i   = 0;
        while (false === $ret) {
            if ($i > 1) {
                break;
            }
            if ($i > 0) {
                sleep(1);
            }
            $ret = $this->baiduCallOnce($url, $args, $method, false, $timeout, $headers);
            ++$i;
        }

        return $ret;
    }



    public function baiduCallOnce($url, $args=null, $method='post', $withCookie = false, $timeout = 10, $headers=[])
    {
        $ch = curl_init();
        if ('post' == $method) {
            $data = $this->baiduConvert($args);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        } else {
            $data = $this->baiduConvert($args);
            if ($data) {
                if (stripos($url, '?') > 0) {
                    $url .= "&$data";
                } else {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (! empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($withCookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);

        return $r;
    }



    public function baiduConvert(&$args)
    {
        $data = '';
        if (is_array($args)) {
            foreach ($args as $key=>$val) {
                if (is_array($val)) {
                    foreach ($val as $k=>$v) {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                } else {
                    $data .= "$key=".rawurlencode($val).'&';
                }
            }

            return trim($data, '&');
        }

        return $args;
    }
}
