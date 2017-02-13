jQuery(document).ready ($) ->
  BBModalView = window.Backbone.View.extend(
      el: '',
      overlay: false,
      open: ->
        @$response.html ''
        @$el.show()
        @$input.focus()
        if !@$overlay.length
          $('body').append '<div id="' + @el + '-overlay" class="ui-find-overlay"></div>'
          @$overlay = $("##{@el}-overlay")
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
            action: 'find_datask_tax'
            user: window.dataskuserid
            _ajax_nonce: $("##{@el} #_ajax_nonce").val()).always(->
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
        evt.preventDefault()
        @$checked = $('#find-datask-tax-response input[name="found_tax_task"]:checked')
        checked = @$checked.map(->
          @value
        ).get()
        if !checked.length
          @close()
          return
        label = []
        $.each checked, (index, value) ->
          label.push $(@el + '#bb-modal-view-response input#found-' + value).attr 'value'
          return
        $.ajax(ajaxurl,
          type: 'POST'
          dataType: 'json'
          data:
            taxs: label.join(', ')
            user: window.dataskuserid
            action: 'add_datask_tax'
            _ajax_nonce: $(@el + ' #_ajax_nonce').val()).always(->
          search.$spinner.hide()
          return
        ).fail ->
          search.$response.text 'Error'
          return
        @close()
      events: ->
        {
          "keypress #{@el}#bb-modal-view-input": 'maybeStartSearch'
          "keyup #{@el}#bb-modal-view-input": 'escClose'
          "click #{@el}#bb-modal-view-submit": 'selectPost'
          "click #{@el}#bb-modal-view-search": 'send'
          "click #{@el}#bb-modal-view-close": 'close'
        }
      initialize: (id)->
        @modal_id = id
        @el = '#bb-modal-view-' + @modal_id
        @$response = $(@el).find('#bb-modal-view-response')
        @$overlay = $('#bb-modal-view-overlay')
        @$input = $(@el).find('#bb-modal-view-input')
        @$spinner = $(@el).find('.spinner');
        @listenTo this, 'open', @open
        @listenTo this, 'close', @close
    )
  console.log(123)
  $('.bb-modal-button').on 'click', (e) ->
      window.dataskuserid = $(this).data('user-id')
      bb_modal = new BBModalView($(this).data('id'))
      bb_modal.trigger 'open'

