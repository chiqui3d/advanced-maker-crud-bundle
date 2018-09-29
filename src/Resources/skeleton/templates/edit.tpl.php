{% extends 'layout/base.html.twig' %}
{% form_theme form 'form/fields.html.twig' %}

{% block body -%}
<div id="header_wrapper" class="header-md" {% if configuration("general","background") %} style="background-image:url({{asset('photos/' ~ configuration("general","background"))}})!important;" {% endif %}>
    <div class="container">
        <div class="row">
        <div class="col-xs-12">
            <header id="header">
            <h1>Editar <?=ucfirst($singularName); ?></h1>
            <ol class="breadcrumb">
              <li><a href="{{ url('index')}}">Dashboard</a></li>
              <li><a href="{{path('<?= $route_name; ?>_index')}}"><?= $pluralName; ?></a></li>
              <li class="active">Editar</li>
            </ol>
            </header>
        </div>
        </div>
    </div>
</div>
<div id="content" class="container">
<div class="row">
    <div class="col-xs-12">
    <div class="card">
        <header class="card-heading ">
        <h2 class="card-title">Editar <?=ucfirst($singularName); ?></h2>
        <small></small>
        <ul class="card-actions icons right-top">
            <li>
                <a href="{{path('<?= $route_name; ?>_index')}}">
                    <i class="zmdi zmdi-long-arrow-return"></i>
                </a>
            </li>
        </ul>
        </header>
        <div class="card-body">
           {{ form_start(form, {'action': path('<?= $route_name; ?>_edit',{'<?= $entity_identifier; ?>': <?= $entity_var_singular; ?>.<?= $entity_identifier; ?>}), 'method': 'post', 'attr': {'class': 'edit_form', 'autocomplete': 'off', 'enctype':'multipart/form-data'}}) }}
             <div class="col-md-12">
            <? foreach ($form_fields as $field){?>
            {{ form_row(form.<?=$field?>) }}
            <?}?>
            </div>
             {{ form_rest(form) }} 
            <div class="clearfix"></div>
            <div class="card-footer m-t-40 m-b-10 border-top">
                <div class="pull-right mt-sm">
                    <input id="submit" class="btn btn-green" type="submit" value="Actualizar <?=ucfirst($singularName); ?>" />
                </div>
            </div>
            {{ form_end(form) }}
        </div>
    </div>
    </div>
</div>
</div>
{% endblock body %}
{% block customjavascripts %} 
<script>
    $(document).ready(function () {

        $(".edit_form").validate({
            focusInvalid: false,
            ignore: [], // <- proper format to set ignore to "nothing"
            errorPlacement: function (error, element) {
                if ($(element).is("select")) {
                    if($(element).hasClass("chosen-select")){
                        error.insertAfter(element.next()).css({"display":"block","margin": "10px 0"}); // custom error placement
                    }else{
                        error.insertAfter(element).css({"display":"block","margin": "10px 0"}); // custom error placement
                    }
                } else {
                    error.insertAfter(element);  // default error placement
                }
            },
            invalidHandler: function(form, validator) {
                if (!validator.numberOfInvalids())
                    return;
                $('html, body').animate({
                    scrollTop: $(validator.errorList[0].element).offset().top - 350
                }, 1000);
            }
        });

    });
</script>
{% endblock %}