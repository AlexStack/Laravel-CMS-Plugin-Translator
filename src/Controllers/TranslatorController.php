<?php

namespace Amila\LaravelCms\Plugins\Translator\Controllers;

use AlexStack\LaravelCms\Helpers\LaravelCmsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslatorController extends Controller
{
    private $user = null;
    private $helper;

    private $app_id          = null;
    private $app_key         = null;
    private $google_lang_ary = ['en'=>'English', 'zh'=>'Chinese', 'es'=>'Spanish', 'ar'=>'Arabic', 'ja'=>'Japanese', 'hi'=>'Hindi', 'pt'=>'Portuguese', 'fr'=>'French', 'ru'=>'Russian', 'de'=>'German', 'ko'=>'Korean', 'it'=>'Italian', 'la'=>'Latin'];
    private $baidu_lang_ary  = ['en'=>'English', 'zh'=>'Chinese', 'epa'=>'Spanish', 'ara'=>'Arabic', 'jp'=>'Japanese', 'hi'=>'Hindi', 'pt'=>'Portuguese', 'fra'=>'French', 'ru'=>'Russian', 'de'=>'German', 'kor'=>'Korean', 'it'=>'Italian', 'th'=>'Thai'];

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
        // set a default select box value to the form without change the blade includes
        // any better idea than change the $_GET? request()->merge() not working

        $_GET['translate_to']   = $_COOKIE['translate_to'] ?? $this->helper->s('template.frontend_language');
        $_GET['translate_from'] = $_COOKIE['translate_from'] ?? 'en';

        $_GET['append_source_content'] = $_COOKIE['append_source_content'] ?? 'yes';

        $_GET['translate_result_add_to_field'] = $_COOKIE['translate_result_add_to_field'] ?? 'main_content';

        if ('baidu' == strtolower($plugin_settings['api_provider'])) {
            $data['translate_languages'] = $this->baidu_lang_ary;
        } else {
            $data['translate_languages'] = $this->google_lang_ary;
        }

        return $data;
    }

    public function edit($id, $page, $plugin_settings)
    {
        $page->translate_to = $_COOKIE['translate_to'] ?? $this->helper->s('template.frontend_language');

        $page->translate_from = $_COOKIE['translate_from'] ?? 'en';

        $page->append_source_content = $_COOKIE['append_source_content'] ?? 'yes';

        $page->translate_result_add_to_field = $_COOKIE['translate_result_add_to_field'] ?? 'main_content';

        if ('baidu' == strtolower($plugin_settings['api_provider'])) {
            $data['translate_languages'] = $this->baidu_lang_ary;
        } else {
            $data['translate_languages'] = $this->google_lang_ary;
        }

        if ('translateCmsLangFile' == request()->action) {
            $this->translateCmsLangFile();
        }

        return $data;
    }

    public function store($form_data, $page, $plugin_settings)
    {
        return $this->update($form_data, $page, $plugin_settings);
    }

    public function update($form_data, $page, $plugin_settings)
    {
        if ('' == trim($form_data['translate_content'])) {
            return false;
        }
        if ('baidu' == $plugin_settings['api_provider']) {
            $this->app_id  = $plugin_settings['app_id'];
            $this->app_key = $plugin_settings['app_key'];

            $api_result = $this->baiduTranslate($form_data['translate_content'], $form_data['translate_from'], $form_data['translate_to']);
            if (isset($api_result['trans_result'][0]['dst'])) {
                $translate_result = '<div class="translate-content">';
                foreach ($api_result['trans_result'] as $rs) {
                    if ('yes' == $form_data['append_source_content']) {
                        $translate_result .= '<div class="src">'.$rs['src'].'</div>';
                    }
                    $translate_result .= ''.$rs['dst'].'<br/><br/>';
                }
                $translate_result .= '</div>';
                $new_content = $page[$form_data['translate_result_add_to_field']].$translate_result;
            } else {
                if (request()->debug) {
                    $this->helper->debug($api_result);
                }

                return false;
            }
        } elseif ('google_free' == $plugin_settings['api_provider']) {
            if ('google_free_002' == $plugin_settings['app_key']) {
                // https://github.com/Stichoza/google-translate-php
                // Need the end-user install via composer first
                try {
                    $tr         = new \Stichoza\GoogleTranslate\GoogleTranslate($form_data['translate_to'], $form_data['translate_from'], ['verify' => false]);
                    $api_result = $tr->translate($form_data['translate_content']);
                } catch (\Exception $e) {
                    echo $plugin_settings['app_key'].'<hr>';
                    exit($e->getMessage());
                }
            } else {
                // https://github.com/dejurin/php-google-translate-for-free
                // Need copy the class code to the GoogleTranslateForFree.php
                try {
                    $tr = new GoogleTranslateForFree();
                    // google_free translate max character limit 5000
                    if (strlen(urlencode($form_data['translate_content'])) >= 5000) {
                        $form_data['translate_content'] = urldecode(substr(urlencode($form_data['translate_content']), 0, 4999));
                    }
                    $api_result = $tr->translate($form_data['translate_from'], $form_data['translate_to'], $form_data['translate_content'], 2);
                } catch (\Exception $e) {
                    echo $plugin_settings['app_key'].'<hr>';
                    exit($e->getMessage());
                }
            }

            if ($api_result) {
                $translate_result = '<div class="translate-content">'.nl2br($api_result).'</div>';

                if ('yes' == $form_data['append_source_content']) {
                    $translate_result .= '<div class="pt-3 source-content"><hr class="source-hr" />'.nl2br($form_data['translate_content']).'</div>';
                }

                $new_content = $page[$form_data['translate_result_add_to_field']].$translate_result;
            }

            // $this->helper->debug([$new_content,$form_data]);
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

    // translate cms.php array one by one via baidu ai
    public function translateCmsLangFile()
    {
        $this->app_id   = $this->helper->s('plugin.page-tab-translator.app_id');
        $this->app_key  = $this->helper->s('plugin.page-tab-translator.app_key');
        $translate_from = request()->translate_from ?? 'en';
        $translate_to   = request()->translate_to ?? 'es';
        $file           = request()->file ?? 'cms.php';
        if (! isset($this->baidu_lang_ary[$translate_to])) {
            exit('wrong $translate_to '.$translate_to);
        }
        if ('zh' == $translate_to || 'en' == $translate_to) {
            exit('Not allowed $translate_to '.$translate_to);
        }
        $source_file = base_path('resources/lang/vendor/laravel-cms/'.$translate_from.'/'.$file);
        $target_file = base_path('resources/lang/vendor/laravel-cms/'.$translate_to.'/'.$file);
        if (! file_exists($source_file)) {
            exit($source_file.' not exists');
        }
        $source_lang_ary = include $source_file;
        $except_ary      = [':Name', ':name', ':latest', ':current', ':number', '$helper->s(', ':BROKER'];
        // $except_ary = [':Name',':name',':latest',':current',':number','$helper->s('];

        $variable_prefix = '567.8.';

        $search_ary  = array_values($except_ary);
        $replace_ary = array_map(function ($v) use ($variable_prefix) {
            return $variable_prefix.$v.'';
        }, array_keys($except_ary));

        $i            = 0;
        $new_lang_ary = [];
        foreach ($source_lang_ary as $key=> $source_lang_str) {
            if (! strpos($source_lang_str, ':')) {
                // continue;
            }

            $source_lang_str = str_replace($search_ary, $replace_ary, $source_lang_str);

            $api_result = $this->baiduTranslate($source_lang_str, $translate_from, $translate_to);

            if (isset($api_result['trans_result'][0]['dst'])) {
                $translate_result = '';
                foreach ($api_result['trans_result'] as $rs) {
                    $translate_result .= ''.trim($rs['dst'])."\n";
                }

                $new_lang_ary[$key] =  trim($translate_result);

                if ('page_number22' == $key) {
                    $this->helper->debug($api_result);
                }
            }

            if ($i > 3) {
                // break;
                // $this->helper->debug($new_lang_ary);
            }

            ++$i;
        }

        $new_lang_str = "<?php \n# This language file was automatically translated by Google AI, please edit it manually if you feel it not accurate \n\n return ".var_export($new_lang_ary, true)."; \n";

        $search_ary   = $replace_ary;
        $replace_ary  = array_values($except_ary);
        $new_lang_str = str_replace($search_ary, $replace_ary, $new_lang_str);

        // $this->helper->debug([$search_ary, $replace_ary,$new_lang_str]);

        if (! file_exists(dirname($target_file))) {
            mkdir(dirname($target_file), 0755);
        }

        file_put_contents($target_file, $new_lang_str);
        echo '<hr>Generated language file: '.$target_file;
        exit();
    }

    public function translateCmsLangFile_google()
    {
        $this->app_id   = $this->helper->s('plugin.page-tab-translator.app_id');
        $this->app_key  = $this->helper->s('plugin.page-tab-translator.app_key');
        $break_str      = '#';
        $break_str_2    = '000 ';
        // $google_lang_ary = [  'ar'=>'Arabic',  'hi'=>'Hindi', 'pt'=>'Portuguese',  'de'=>'German', 'ko'=>'Korean', 'it'=>'Italian', 'la'=>'Latin'];

        $translate_from = request()->translate_from ?? 'en';
        $translate_to   = request()->translate_to ?? 'es';
        $file           = request()->file ?? 'cms.php';
        if (! isset($this->google_lang_ary[$translate_to])) {
            exit('wrong $translate_to '.$translate_to);
        }
        if ('zh' == $translate_to || 'en' == $translate_to) {
            exit('Not allowed $translate_to '.$translate_to);
        }

        $source_file = base_path('resources/lang/vendor/laravel-cms/'.$translate_from.'/'.$file);
        $target_file = base_path('resources/lang/vendor/laravel-cms/'.$translate_to.'/'.$file);
        if (! file_exists($source_file)) {
            exit($source_file.' not exists');
        }
        $source_lang_ary = include $source_file;
        $except_ary      = [':Name', ':name', ':latest', ':current', ':number', '$helper->s(', ':BROKER'];
        // $except_ary = [':Name',':name',':latest',':current',':number','$helper->s('];

        $variable_prefix = '2020778899';

        $source_lang_str = implode($break_str, array_values($source_lang_ary));

        $search_ary  = array_values($except_ary);
        $replace_ary = array_map(function ($v) use ($variable_prefix) {
            return $variable_prefix.$v.'';
        }, array_keys($except_ary));
        $source_lang_str = str_replace($search_ary, $replace_ary, $source_lang_str);

        $api_result = $this->baiduTranslate($source_lang_str, $translate_from, $translate_to);

        try {
            $tr         = new GoogleTranslateForFree();
            $api_result = $tr->translate($translate_from, $translate_to, $source_lang_str, 2);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }

        // $api_result = str_replace($break_str_2, $break_str, $api_result);
        $translated_ary = explode($break_str, $api_result);
        if (count($translated_ary) != count($source_lang_ary)) {
            echo 'count($translated_ary) '.count($translated_ary).' not match count($source_lang_ary) '.count($source_lang_ary);
            $i = 0;
            foreach ($source_lang_ary as $key=>$val) {
                echo $key.' => '.$val.' === '.($translated_ary[$i] ?? 'NONE').'<br>'."\n";
                ++$i;
            }

            $this->helper->debug([$translated_ary, $source_lang_ary, $api_result]);
        }
        $search_ary     = $replace_ary;
        $replace_ary    = array_values($except_ary);
        $new_api_result = str_replace($search_ary, $replace_ary, $api_result);
        $new_api_result = str_replace(':BROKER', '', $new_api_result);

        $translated_ary = explode($break_str, $new_api_result);
        $i              =0;
        $new_lang_ary   = [];
        foreach ($source_lang_ary as $key=>$val) {
            echo $key.' => '.$val.' === '.$translated_ary[$i].'<br>'."\n";
            $new_lang_ary[$key] = trim($translated_ary[$i]);
            ++$i;
        }

        // $this->helper->debug([$search_ary, $replace_ary, $new_lang_ary]);

        $new_lang_str = "<?php \n# This language file was automatically translated by Google AI, please edit it manually if you feel it not accurate \n\n return ".var_export($new_lang_ary, true)."; \n";

        if (! file_exists(dirname($target_file))) {
            mkdir(dirname($target_file), 0755);
        }

        file_put_contents($target_file, $new_lang_str);
        echo '<hr>Generated language file: '.$target_file;
        exit();
    }

    // baidu FanYi translate
    public function baiduTranslate($query, $from, $to)
    {
        $app_id  = $this->app_id;
        $app_key = $this->app_key;
        if (strlen($app_id) < 10 || strlen($app_key) < 10) {
            exit('Please edit the app_id & app_key in the translator plugin setting page, you can get them from https://api.fanyi.baidu.com/');
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
