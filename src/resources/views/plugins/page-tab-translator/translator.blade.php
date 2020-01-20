<div class="row mb-5 pb-5">
    @if ( strlen($helper->s("plugin.page-tab-translator.app_key"))<11 ) <div class="col-md-12 p-3">
        <div class="alert alert-danger" role="alert">
            Please set up the plugin.page-tab-translator.app_key & app_id in
            the CMS settings first.
            <a href="https://github.com/AlexStack/Laravel-CMS-Plugin-Translator#how-to-change-the-settings"
                target="_blank">Tutorial</a>
        </div>
</div>
@endif
<div class="col-md">
    @include($helper->bladePath('includes.form-input','b'), ['name' =>
    "translate_from", 'type'=>'select', 'options'=>$plugins['translator']['translate_languages'] ])
</div>
<div class="col-md">
    @include($helper->bladePath('includes.form-input','b'), ['name' =>
    "translate_to", 'type'=>'select', 'options'=>$plugins['translator']['translate_languages']])
</div>
<div class="col-md">
    @include($helper->bladePath('includes.form-input','b'), ['name' =>
    "translate_result_add_to_field", 'type'=>'select', 'options'=>['main_content' => 'Main Content','sub_content' =>
    'Sub Content', 'extra_content_1'=> 'Extra Content 1', 'extra_content_2'=> 'Extra Content 2', 'extra_content_3'=>
    'Extra Content 3'] ])
</div>
<div class="col-md">
    @include($helper->bladePath('includes.form-input','b'), ['name' =>
    "append_source_content", 'type'=>'select', 'options'=>['yes' => 'Yes',
    'no'=> 'No'] ])
</div>
<div class="col-md-12">
    @include($helper->bladePath('includes.form-input','b'), ['name' =>
    "translate_content", 'type'=>'textarea' ])
</div>
<div class="col-md-12 pb-5 mb-5 text-secondary">
    Translate Content character length: <span id="character-length">0</span> / suggested max 5000
</div>

</div>

<div class="pb-5 mb-5 text-light">
    Translate provider: {{$helper->s('plugin.page-tab-translator.api_provider')}}
    ( {{$helper->s('plugin.page-tab-translator.app_id')}} )
</div>
<script>
    $('.input-translate_content').keyup(function(){
        let len = $('.input-translate_content').val().length;
        let lenStr = len > 5000 ? '<span class="text-danger">'+len+'</span>' : len;
        $('#character-length').html(lenStr);
    })
</script>
