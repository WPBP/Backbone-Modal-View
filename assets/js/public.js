jQuery(document).ready(function($) {
  var BBModalView;
  BBModalView = window.Backbone.View.extend({
    el: '',
    overlay: false,
    open: function() {
      this.$response.html('');
      this.$el.show();
      this.$input.focus();
      if (!this.$overlay.length) {
        $('body').append('<div id="' + this.el + '-overlay" class="ui-find-overlay"></div>');
        this.$overlay = $("#" + this.el + "-overlay");
      }
      this.$overlay.show();
      return this.send();
    },
    send: function() {
      var search;
      search = this;
      search.$spinner.show();
      return $.ajax(ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: {
          ps: search.$input.val(),
          action: 'find_datask_tax',
          user: window.dataskuserid,
          _ajax_nonce: $("#" + this.el + " #_ajax_nonce").val()
        }
      }).always(function() {
        search.$spinner.hide();
      }).done(function(response) {
        var data;
        if (!response.success) {
          search.$response.text('Error');
        }
        data = response.data;
        if ('checkbox' === search.selectType) {
          data = data.replace(/type="radio"/gi, 'type="checkbox"');
        }
        search.$response.html(data);
      }).fail(function() {
        search.$response.text('Error');
      });
    },
    close: function() {
      this.$overlay.hide();
      return this.$el.hide();
    },
    escClose: function(evt) {
      if (evt.which && 27 === evt.which) {
        return this.close();
      }
    },
    maybeStartSearch: function(evt) {
      return this.send();
    },
    selectPost: function(evt) {
      var checked, label, search;
      search = this;
      evt.preventDefault();
      this.$checked = $('#find-datask-tax-response input[name="found_tax_task"]:checked');
      checked = this.$checked.map(function() {
        return this.value;
      }).get();
      if (!checked.length) {
        this.close();
        return;
      }
      label = [];
      $.each(checked, function(index, value) {
        label.push($(this.el + '#bb-modal-view-response input#found-' + value).attr('value'));
      });
      $.ajax(ajaxurl, {
        type: 'POST',
        dataType: 'json',
        data: {
          taxs: label.join(', '),
          user: window.dataskuserid,
          action: 'add_datask_tax',
          _ajax_nonce: $(this.el + ' #_ajax_nonce').val()
        }
      }).always(function() {
        search.$spinner.hide();
      }).fail(function() {
        search.$response.text('Error');
      });
      return this.close();
    },
    events: function() {
      var obj;
      return (
        obj = {},
        obj["keypress " + this.el + "#bb-modal-view-input"] = 'maybeStartSearch',
        obj["keyup " + this.el + "#bb-modal-view-input"] = 'escClose',
        obj["click " + this.el + "#bb-modal-view-submit"] = 'selectPost',
        obj["click " + this.el + "#bb-modal-view-search"] = 'send',
        obj["click " + this.el + "#bb-modal-view-close"] = 'close',
        obj
      );
    },
    initialize: function(id) {
      this.modal_id = id;
      this.el = '#bb-modal-view-' + this.modal_id;
      this.$response = $(this.el).find('#bb-modal-view-response');
      this.$overlay = $('#bb-modal-view-overlay');
      this.$input = $(this.el).find('#bb-modal-view-input');
      this.$spinner = $(this.el).find('.spinner');
      this.listenTo(this, 'open', this.open);
      return this.listenTo(this, 'close', this.close);
    }
  });
  console.log(123);
  return $('.bb-modal-button').on('click', function(e) {
    var bb_modal;
    window.dataskuserid = $(this).data('user-id');
    bb_modal = new BBModalView($(this).data('id'));
    return bb_modal.trigger('open');
  });
});

//# sourceMappingURL=public.js.map
