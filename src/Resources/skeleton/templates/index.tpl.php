{% extends 'layout/base.html.twig' %}

{% block body %}
<div id="header_wrapper" class="header-lg overlay ecom-header" {% if configuration("general","background") %} style="background-image:url({{asset('photos/' ~ configuration("general","background"))}})!important;" {% endif %}>
    <div class="container-fluid">
      <div class="row">
        <div class="col-xs-12">
          <header id="header">
            <h1><?= $pluralName; ?></h1>
            <ol class="breadcrumb">
              <li><a href="{{ url('index')}}">Dashboard</a></li>
              <li><a href="javascript:void(0)"><?= $pluralName; ?></a></li>
              <li class="active">Listado</li>
            </ol>
          </header>
        </div>
      </div>
    </div>
  </div>
  <div id="content" class="container-fluid">
    <div class="content-body">
    <div class="row">
      <div class="col-xs-12">
        <div class="card card-data-tables product-table-wrapper">
          <header class="card-heading">
            <h2 class="card-title">Administrar <?= $pluralName; ?></h2>
            <p></p>
            {{include('<?= $route_name; ?>/search_form.html.twig') }}
          </header>
          <div class="card-body p-0">
            <div class="text-right">
                <a href="#" class="btn btn-info btn-fab animate-fab add_new search-toogle">
                  <i class="zmdi zmdi-search"></i>
                </a>
                <a href="{{ url('<?= $route_name; ?>_new')}}" class="btn btn-primary btn-fab  animate-fab add_new">
                  <i class="zmdi zmdi-plus"></i>
                </a>
            </div>
            <div class="row">
              <div class="col-sm-12">
                <div class="countLeft text-left">
                    <p>Mostrando la pagina {{ pagination.current }} un total de {{pagination.pageCount}} pagina y {{ pagination.count }} items</p>
                </div>
              </div>  
            </div>
            <div class="table-responsive">
              <table id="productsTable" class="mdl-data-table product-table m-t-30" cellspacing="0" width="100%">
                <thead>
                  <tr>
                    <?php foreach ($entity_fields as $field): ?>
                    <th>
                    {{ include('pagination/sortable.html.twig', { 'pagination': pagination, 'option': '<?=$shortEntity?>.<?=$field['fieldName']?>','name': '<?=ucfirst($field['fieldName'])?>' }) }} </th>
                    <?php endforeach; ?>
                    <th data-orderable="false" >Acciones</th>
                  </tr>
                </thead>
                <tbody>
                {% for <?= $entity_var_singular; ?> in pagination %}
                  <tr data-tr="{{<?= $entity_var_singular; ?>.<?= $entity_identifier; ?>}}">
                    <?php foreach ($entity_fields as $field): ?>
                    <td>{{<?=$helper->getEntityFieldPrintCode($entity_var_singular, $field); ?>}}</td>
                    <?php endforeach; ?>
                    <td>
                        <a href="{{ path('<?= $route_name; ?>_edit', {'<?= $entity_identifier; ?>': <?= $entity_var_singular; ?>.<?= $entity_identifier; ?>} ) }}" class="iconrounded">
                          <i class="zmdi zmdi-edit"></i>
                        </a>
                        <a href="#" data-url-ajax="{{ path('<?= $route_name; ?>_remove_ajax') }}" class="iconrounded confirmSingleDelete">
                          <i class="zmdi zmdi-delete"></i>
                        </a>
                    </td>
                  </tr>
                {% endfor %}
                </tbody>
              </table>
            </div>
             <div class="row">
              <div class="col-sm-12">
                <div class="navigation text-right">
                     {{ include('pagination/pager.html.twig', { 'pagination': pagination }) }}
                </div>
              </div>  
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{% endblock %}