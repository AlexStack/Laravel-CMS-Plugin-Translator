<div class="row mb-5 pb-5">
    <div class="col-md-6">
        @include($helper->bladePath('includes.form-input','b'), ['name' =>
        "translate_from", 'type'=>'select', 'options'=>$plugins['translator']['translate_languages'] ])
    </div>
    <div class="col-md-6">
        @include($helper->bladePath('includes.form-input','b'), ['name' =>
        "translate_to", 'type'=>'select', 'options'=>$plugins['translator']['translate_languages']])
    </div>
    <div class="col-md-12">
        @include($helper->bladePath('includes.form-input','b'), ['name' =>
        "translate_content", 'type'=>'textarea' ])
    </div>

    <div class="col-md-6">
        @include($helper->bladePath('includes.form-input','b'), ['name' =>
        "translate_result_add_to_field", 'type'=>'select', 'options'=>['main_content' => 'Main Content','sub_content' =>
        'Sub Content', 'extra_content_1'=> 'Extra Content 1', 'extra_content_2'=> 'Extra Content 2', 'extra_content_3'=>
        'Extra Content 3'] ])
    </div>
    <div class="col-md-6 mb-5">
        @include($helper->bladePath('includes.form-input','b'), ['name' =>
        "append_source_content", 'type'=>'select', 'options'=>['yes' => 'Yes',
        'no'=> 'No'] ])
    </div>

</div>
