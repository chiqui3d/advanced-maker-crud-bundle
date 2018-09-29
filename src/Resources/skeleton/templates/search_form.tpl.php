{% form_theme searchForm 'form/fields.html.twig' %}

<div class="row">
	<div class="col-md-12 buscador">
        <div class="card">
            <div class="card-heading card-default">
                <h2 class="card-title">Buscador</h2>
            </div>
            <div class="card-body">
            {{ form_start(searchForm, {'method': 'post', 'action': path('<?= $route_name; ?>_index'), 'attr': {'novalidate': 'novalidate','class': '<?= $route_name; ?>_search_list', 'autocomplete': 'off', 'novalidate':'','enctype':'multipart/form-data'}}) }}
                <div class="row">
                    <div class="col-md-12">
                    <? foreach ($form_fields as $field){?>
                    {{ form_row(searchForm.<?=$field?>) }}
                    <?}?>
                    </div>
                {{ form_rest(searchForm) }} 
                </div>		
                <div class="card-footer">
                    <div class="pull-right mt-sm">
                        <button type="submit" class="btn btn-green">
                            Buscar
                        </button>
                    </div>
                </div>
            {{ form_end(searchForm) }}
            </div>
        </div>
    </div>
</div>