(function ($) {
  'use strict';

  var iconPickerModal = null;
  var currentIconInput = null;
  var searchTimeout = null;

  function initRepeater(control) {
    var $control = $(control.container);
    var $list = $control.find('.atalhos-repeater-list');
    var $value = $control.find('.atalhos-repeater-value');
    var $addBtn = $control.find('.atalhos-add-item');

    function getData() {
      try {
        var val = $value.val();
        if (!val) return [];
        return JSON.parse(val);
      } catch (e) {
        return [];
      }
    }

    function setData(data) {
      $value.val(JSON.stringify(data));
      $value.trigger('change');
      if (wp.customize && wp.customize.instance) {
        var settingId = $value.attr('data-customize-setting-link');
        if (settingId) {
          var setting = wp.customize.instance(settingId);
          if (setting) {
            setting.set(JSON.stringify(data));
          }
        }
      }
    }

    function renderItem(item, index) {
      var icon = item.icon || '';
      var color = item.color || '#607D8B';
      return '' +
        '<li class="atalhos-repeater-item" data-index="' + index + '">' +
          '<span class="atalhos-repeater-handle" title="Arrastar para reordenar"><span class="dashicons dashicons-menu"></span></span>' +
          '<div class="atalhos-repeater-fields">' +
            '<div class="atalhos-repeater-field">' +
              '<input type="text" class="atalhos-label" value="' + escAttr(item.label || '') + '" placeholder="Nome do atalho" />' +
            '</div>' +
            '<div class="atalhos-repeater-field">' +
              '<input type="text" class="atalhos-url" value="' + escAttr(item.url || '') + '" placeholder="/url-ou-https://..." />' +
            '</div>' +
            '<div class="atalhos-repeater-field-row">' +
              '<div class="atalhos-repeater-field atalhos-icon-field">' +
                '<input type="text" class="atalhos-icon" value="' + escAttr(icon) + '" placeholder="fas fa-home" readonly />' +
                '<button type="button" class="button atalhos-pick-icon" title="Escolher ícone"><span class="dashicons dashicons-art"></span></button>' +
              '</div>' +
              '<div class="atalhos-repeater-field atalhos-color-field">' +
                '<input type="color" class="atalhos-color" value="' + escAttr(color) + '" />' +
              '</div>' +
            '</div>' +
            '</div>' +
          '<div class="atalhos-repeater-remove-wrapper">' +
            '<button type="button" class="button atalhos-remove-item"><span class="dashicons dashicons-no-alt"></span> Remover atalho</button>' +
          '</div>' +
        '</li>';
    }

    function render() {
      var data = getData();
      $list.empty();
      $.each(data, function (i, item) {
        $list.append(renderItem(item, i));
      });
      if (data.length === 0) {
        $list.append('<li class="atalhos-repeater-empty">Nenhum atalho configurado. Clique em "Adicionar Atalho" para começar.</li>');
      }
      updateColors(data);
    }

    function updateColors(data) {
      var n = 1;
      $.each(data, function (i, item) {
        var color = item.color || '#607D8B';
        var $item = $list.find('.atalhos-repeater-item').eq(i);
        $item.find('.atalhos-repeater-handle').css({
          'background': color,
          'color': '#fff'
        });
        n++;
      });
    }

    function addItem(data) {
      if (!data) {
        data = { label: '', url: '', icon: '', color: '#607D8B' };
      }
      var items = getData();
      items.push(data);
      setData(items);
      render();
    }

    $addBtn.on('click', function () {
      addItem();
    });

    $list.on('click', '.atalhos-remove-item', function () {
      var $item = $(this).closest('.atalhos-repeater-item');
      var idx = $item.index();
      var items = getData();
      items.splice(idx, 1);
      setData(items);
      render();
    });

    $list.on('input change', '.atalhos-label, .atalhos-url, .atalhos-icon, .atalhos-color', function () {
      var $item = $(this).closest('.atalhos-repeater-item');
      var idx = $item.index();
      var items = getData();
      if (items[idx]) {
        items[idx].label = $item.find('.atalhos-label').val();
        items[idx].url = $item.find('.atalhos-url').val();
        items[idx].icon = $item.find('.atalhos-icon').val();
        items[idx].color = $item.find('.atalhos-color').val();
        setData(items);
        updateColors(items);
      }
    });

    $list.sortable({
      handle: '.atalhos-repeater-handle',
      axis: 'y',
      update: function () {
        var items = getData();
        var reordered = [];
        $list.find('.atalhos-repeater-item').each(function () {
          var idx = $(this).data('index');
          reordered.push(items[idx]);
        });
        setData(reordered);
        render();
      }
    });

    render();
  }

  function escAttr(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function buildIconPicker(icons) {
    if (iconPickerModal) return iconPickerModal;

    var modal = $('' +
      '<div class="atalhos-icon-modal">' +
        '<div class="atalhos-icon-modal-backdrop"></div>' +
        '<div class="atalhos-icon-modal-content">' +
          '<div class="atalhos-icon-modal-header">' +
            '<h3>Selecionar Ícone</h3>' +
            '<input type="text" class="atalhos-icon-search" placeholder="Buscar ícones..." />' +
            '<button type="button" class="button media-modal-close atalhos-icon-close"><span class="media-modal-icon"></span></button>' +
          '</div>' +
          '<div class="atalhos-icon-modal-body">' +
            '<div class="atalhos-icon-grid"></div>' +
          '</div>' +
        '</div>' +
      '</div>');

    var $grid = modal.find('.atalhos-icon-grid');
    var $search = modal.find('.atalhos-icon-search');

    function renderIcons(filter) {
      $grid.empty();
      var filtered = icons;
      if (filter) {
        var f = filter.toLowerCase();
        filtered = icons.filter(function (icon) {
          return icon.indexOf(f) !== -1;
        });
      }
      if (filtered.length === 0) {
        $grid.append('<p class="atalhos-icon-no-results">Nenhum ícone encontrado.</p>');
        return;
      }
      $.each(filtered, function (i, icon) {
        var iconShort = icon.replace(/^fa-?[srb]?\s+/i, '').replace(/^fa-solid\s+/i, '').replace(/^fa-regular\s+/i, '').replace(/^fa-brands\s+/i, '');
        $grid.append('' +
          '<div class="atalhos-icon-item" data-icon="' + escAttr(icon) + '" title="' + escAttr(icon) + '">' +
            '<i class="' + escAttr(icon) + '"></i>' +
            '<span>' + escAttr(iconShort) + '</span>' +
          '</div>');
      });
    }

    $search.on('input', function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function () {
        renderIcons($search.val());
      }, 200);
    });

    $grid.on('click', '.atalhos-icon-item', function () {
      var icon = $(this).data('icon');
      if (currentIconInput) {
        currentIconInput.val(icon);
        currentIconInput.trigger('input');
      }
      closeIconPicker();
    });

    modal.on('click', '.atalhos-icon-close, .atalhos-icon-modal-backdrop', function () {
      closeIconPicker();
    });

    iconPickerModal = modal;
    return modal;
  }

  function openIconPicker(input) {
    currentIconInput = input;
    var modal = buildIconPicker(intranetAtalhos.icons);
    modal.find('.atalhos-icon-search').val('');
    modal.find('.atalhos-icon-grid').empty();
    renderIconsWithDelay(modal, '');
    $('body').append(modal);
    modal.fadeIn(200);
  }

  function renderIconsWithDelay(modal, filter) {
    var $grid = modal.find('.atalhos-icon-grid');
    var icons = intranetAtalhos.icons;
    var filtered = filter ? icons.filter(function (i) { return i.indexOf(filter.toLowerCase()) !== -1; }) : icons;
    $grid.empty();
    if (filtered.length === 0) {
      $grid.append('<p class="atalhos-icon-no-results">Nenhum ícone encontrado.</p>');
      return;
    }
    $.each(filtered, function (i, icon) {
      var iconShort = icon.replace(/^fa-?[srb]?\s+/i, '').replace(/^fa-solid\s+/i, '').replace(/^fa-regular\s+/i, '').replace(/^fa-brands\s+/i, '');
      $grid.append('' +
        '<div class="atalhos-icon-item" data-icon="' + escAttr(icon) + '" title="' + escAttr(icon) + '">' +
          '<i class="' + escAttr(icon) + '"></i>' +
          '<span>' + escAttr(iconShort) + '</span>' +
        '</div>');
    });
  }

  function closeIconPicker() {
    if (iconPickerModal) {
      iconPickerModal.fadeOut(200, function () {
        iconPickerModal.remove();
      });
    }
    currentIconInput = null;
  }

  $(document).on('click', '.atalhos-pick-icon', function (e) {
    e.preventDefault();
    var $input = $(this).closest('.atalhos-icon-field').find('.atalhos-icon');
    openIconPicker($input);
  });

  wp.customize.controlConstructor['atalhos_repeater'] = wp.customize.Control.extend({
    ready: function () {
      initRepeater(this);
    }
  });

})(jQuery);
