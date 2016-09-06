<?php

namespace teliasorg\autocompleteAjax;

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class AutocompleteAjax extends InputWidget
{
    public $multiple = false;
    public $url = [];
    public $options = [];
    public $class = '';
    public $id = null;
    public $varName = null;

    private $_baseUrl;
    private $_ajaxUrl;

    public function registerActiveAssets()
    {
        if ($this->_baseUrl === null) {
            $this->_baseUrl = ActiveAssets::register($this->getView())->baseUrl;
        }
        return $this->_baseUrl;
    }

    public function getUrl()
    {
        if ($this->_ajaxUrl === null) {
            $this->_ajaxUrl = Url::toRoute($this->url);
        }
        return $this->_ajaxUrl;
    }

    public function run()
    {
        $value = $this->model->{$this->attribute};
        $this->registerActiveAssets();

        $onChangeJs = '';

        if (isset($this->options['onChange'])) {
            $onChangeJs = $this->options['onChange'];
        }

        if (isset($this->options['class'])) {
            $this->class = $this->options['class'];
        } else {
            $this->class = 'form-control';
        }

        if ($this->options['id']) {
            $this->id = $this->options['id'];
            $this->varName = str_replace('-', '_', $this->options['id']);
        } else {
            $this->id = $this->varName = $this->getId();
        }

        if ($this->multiple) {

            $this->getView()->registerJs("

                $('#{$this->getId()}').keyup(function(event) {
                    if (event.keyCode == 8 && !$('#{$this->id}').val().length) {

                        $('#{$this->id}-hidden').val('');

                    } else if ($('.ui-autocomplete').css('display') == 'none' &&
                        $('#{$this->id}-hidden').val().split(', ').length > $(this).val().split(', ').length) {

                        var val = $('#{$this->id}').val().split(', ');
                        var ids = [];
                        for (var i = 0; i<val.length; i++) {
                            val[i] = val[i].replace(',', '').trim();
                            ids[i] = cache_{$this->id}_1[val[i]];
                        }
                        $('#{$this->id}-hidden').val(ids.join(', '));
                    }
                });

                $('#{$this->id}').keydown(function(event) {

                    if (event.keyCode == 13 && $('.ui-autocomplete').css('display') == 'none') {
                        submit_{$this->varName} = $('#{$this->id}').closest('.grid-view');
                        $('#{$this->id}').closest('.grid-view').yiiGridView('applyFilter');
                    }

                    if (event.keyCode == 13) {
                        $('.ui-autocomplete').hide();
                    }

                });

                $('body').on('beforeFilter', '#' + $('#{$this->id}').closest('.grid-view').attr('id') , function(event) {
                    return submit_{$this->varName};
                });

                var submit_{$this->varName} = false;
                var cache_{$this->varName} = {};
                var cache_{$this->varName}_1 = {};
                var cache_{$this->varName}_2 = {};
                jQuery('#{$this->id}').autocomplete(
                {
                    minLength: 1,
                    source: function( request, response )
                    {
                        var term = request.term;

                        if (term in cache_{$this->varName}) {
                            response( cache_{$this->varName}[term]);
                            return;
                        }
                        $.getJSON('{$this->getUrl()}', request, function( data, status, xhr ) {
                            cache_{$this->varName} [term] = data;

                            for (var i = 0; i<data.length; i++) {
                                if (!(data[i].id in cache_{$this->varName}_2)) {
                                    cache_{$this->varName}_1[data[i].label] = data[i].id;
                                    cache_{$this->varName}_2[data[i].id] = data[i].label;
                                }
                            }

                            response(data);
                        });
                    },
                    select: function(event, ui)
                    {
                        var val = $('#{$this->id}-hidden').val().split(', ');

                        if (val[0] == '') {
                            val[0] = ui.item.id;
                        } else {
                            val[val.length] = ui.item.id;
                        }

                        $('#{$this->id}-hidden').val(val.join(', '));

                        var names = [];
                        for (var i = 0; i<val.length; i++) {
                            names[i] = cache_{$this->varName}_2[val[i]];
                        }

                        setTimeout(function() {
                            $('#{$this->id}').val(names.join(', '));
                        }, 0);

                        {$onChangeJs}
                    }
                });
            ");
        } else {
            $this->getView()->registerJs("
                var cache_{$this->varName} = {};
                var cache_{$this->varName}_1 = {};
                var cache_{$this->varName}_2 = {};
                jQuery('#{$this->id}').autocomplete(
                {
                    minLength: 1,
                    source: function( request, response )
                    {
                        var term = request.term;
                        if ( term in cache_{$this->varName} ) {
                            response( cache_{$this->varName} [term] );
                            return;
                        }
                        $.getJSON('{$this->getUrl()}', request, function( data, status, xhr ) {
                            cache_{$this->varName} [term] = data;
                            response(data);
                        });
                    },
                    select: function(event, ui)
                    {
                        $('#{$this->id}-hidden').val(ui.item.id);
                        {$onChangeJs}
                    }
                });
            ");
        }

        if ($value) {
            $this->getView()->registerJs("
                $(function(){
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        url: '{$this->getUrl()}',
                        data: {term: '$value'},
                        success: function(data) {

                            if (data.length == 0) {
                                $('#{$this->id}').attr('placeholder', 'User not found !!!');
                            } else {
                                var arr = [];
                                for (var i = 0; i<data.length; i++) {
                                    arr[i] = data[i].label;
                                    if (!(data[i].id in cache_{$this->varName}_2)) {
                                        cache_{$this->varName}_1[data[i].label] = data[i].id;
                                        cache_{$this->varName}_2[data[i].id] = data[i].label;
                                    }
                                }
                                $('#{$this->id}').val(arr.join(', '));
                            }
                            $('.autocomplete-image-load').hide();
                        }
                    });
                });
            ");
        }

        return Html::tag('div',

            Html::activeHiddenInput($this->model, $this->attribute, ['id' => $this->id . '-hidden', 'class' => $this->class])
            . ($value ? Html::tag('div', "<img src='{$this->registerActiveAssets()}/images/load.gif'/>", ['class' => 'autocomplete-image-load']) : '')
            . Html::textInput('', '', array_merge($this->options, ['id' => $this->id, 'class' => $this->class]))

            , [
                'style' => 'position: relative;'
            ]
        );
    }
}
