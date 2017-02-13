jQuery(document).ready ($) ->
  BBModalView = window.Backbone.View.extend(
      overlay: false,
      open: ->
        @$response.html ''
        @$el.show()
        @$input.focus()
        @$overlay.show()
        # Pull some results up by default
        @send()
      send: ->
        search = this
        search.$spinner.show()
        $.ajax(ajaxurl,
          type: 'POST'
          dataType: 'json'
          data:
            ps: search.$input.val()
            action: $(".modal-#{@modal_id}").data('ajax')
            _ajax_nonce: $("#{@selector} #_ajax_nonce").val()).always(->
          search.$spinner.hide()
          return
        ).done((response) ->
          if !response.success
            search.$response.text 'Error'
          data = response.data
          if 'checkbox' == search.selectType
            data = data.replace(/type="radio"/gi, 'type="checkbox"')
          search.$response.html data
          return
        ).fail ->
          search.$response.text 'Error'
          return
      close: ->
        @$overlay.hide()
        @$el.hide()
      escClose: (evt)->
        if evt.which and 27 == evt.which
          @close()
      maybeStartSearch: (evt)->
        @send()
      selectPost: (evt)->
        search = this
        selector = @selector
        evt.preventDefault()
        @$checked = $(@selector + ' #bb-modal-view-response input[name="' + $(".modal-#{@modal_id}").data('ajax') + '"]:checked')
        checked = @$checked.map(->
          @value
        ).get()
        if !checked.length
          @close()
          return
        label = []
        $.each checked, (index, value) ->
          label.push $(selector + ' #bb-modal-view-response input#found-' + value).attr 'value'
          return
        if !!$(".modal-#{@modal_id}").data('ajax-on-select') 
            $.ajax(ajaxurl,
            type: 'POST'
            dataType: 'json'
            data:
                check: label.join(', ')
                action: $(".modal-#{@modal_id}").data('ajax-on-select')
                _ajax_nonce: $(@selector + ' #_ajax_nonce').val()).always(->
                    search.$spinner.hide()
                    return
            ).fail ->
                search.$response.text 'Error'
                return
        @close()
      events: ->
        {
          "keypress #bb-modal-view-input": 'maybeStartSearch'
          "keyup #bb-modal-view-input": 'escClose'
          "click #bb-modal-view-submit": 'selectPost'
          "click #bb-modal-view-search": 'send'
          "click #bb-modal-view-close": 'close'
        }
      initialize: (pars)->
        @$el = $(@el)
        @selector = pars.selector
        @modal_id = pars.modal_id
        @$response = @$el.find('#bb-modal-view-response')
        @$input = @$el.find('#bb-modal-view-input')
        @$spinner = @$el.find('.spinner');
        @listenTo this, 'open', @open
        @listenTo this, 'close', @close
        @$overlay = $('#' + @modal_id + '-overlay')
        if !@$overlay.length
            $('body').append '<div id="' + @modal_id + '-overlay" class="ui-find-overlay"></div>'
            @$overlay = $('#' + @modal_id + '-overlay')
    )
  
  $('.bb-modal-button').on 'click', (e) ->
      bb_modal = new BBModalView( {el:'#bb-modal-view-' + $(this).data('id'), modal_id:$(this).data('id'), selector:'#bb-modal-view-' + $(this).data('id') } )
      bb_modal.trigger 'open'

