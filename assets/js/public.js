jQuery(document).ready(function($) {
  var BBModalView;
  BBModalView = window.Backbone.View.extend({
    overlay: false,
    open: function() {
      this.$response.html('');
      this.$el.show();
      this.$input.focus();
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
          action: $(".modal-" + this.modal_id).data('ajax'),
          _ajax_nonce: $(this.selector + " #_ajax_nonce").val()
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
      var checked, label, search, selector;
      search = this;
      selector = this.selector;
      evt.preventDefault();
      this.$checked = $(this.selector + ' #bb-modal-view-response input[name="' + $(".modal-" + this.modal_id).data('ajax') + '"]:checked');
      checked = this.$checked.map(function() {
        return this.value;
      }).get();
      if (!checked.length) {
        this.close();
        return;
      }
      label = [];
      $.each(checked, function(index, value) {
        label.push($(selector + ' #bb-modal-view-response input#found-' + value).attr('value'));
      });
      if (!!$(".modal-" + this.modal_id).data('ajax-on-select')) {
        $.ajax(ajaxurl, {
          type: 'POST',
          dataType: 'json',
          data: {
            check: label.join(', '),
            action: $(".modal-" + this.modal_id).data('ajax-on-select'),
            _ajax_nonce: $(this.selector + ' #_ajax_nonce').val()
          }
        }).always(function() {
          search.$spinner.hide();
        }).fail(function() {
          search.$response.text('Error');
        });
      }
      return this.close();
    },
    events: function() {
      return {
        "keypress #bb-modal-view-input": 'maybeStartSearch',
        "keyup #bb-modal-view-input": 'escClose',
        "click #bb-modal-view-submit": 'selectPost',
        "click #bb-modal-view-search": 'send',
        "click #bb-modal-view-close": 'close'
      };
    },
    initialize: function(pars) {
      this.$el = $(this.el);
      this.selector = pars.selector;
      this.modal_id = pars.modal_id;
      this.$response = this.$el.find('#bb-modal-view-response');
      this.$input = this.$el.find('#bb-modal-view-input');
      this.$spinner = this.$el.find('.spinner');
      this.listenTo(this, 'open', this.open);
      this.listenTo(this, 'close', this.close);
      this.$overlay = $('#' + this.modal_id + '-overlay');
      if (!this.$overlay.length) {
        $('body').append('<div id="' + this.modal_id + '-overlay" class="ui-find-overlay"></div>');
        return this.$overlay = $('#' + this.modal_id + '-overlay');
      }
    }
  });
  return $('.bb-modal-button').on('click', function(e) {
    var bb_modal;
    bb_modal = new BBModalView({
      el: '#bb-modal-view-' + $(this).data('id'),
      modal_id: $(this).data('id'),
      selector: '#bb-modal-view-' + $(this).data('id')
    });
    return bb_modal.trigger('open');
  });
});

//# sourceMappingURL=public.js.map
