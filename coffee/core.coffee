CONFIG =
    $cont: $('#block_container')
    baseUrl: '/'

TEMPLATES = 
    about: $('#about').html()
    block: $('#block').html()
    modal: $('#modal').html()

Bootstrap = 
    Collections: {}
    Models: {}
    Views: {}

Modal = (apiPath) ->
    
    ModalView = Backbone.View.extend
        el: 'body'
        template: TEMPLATES.modal
        initialize: ->
            @render()                      
            return
        render: ->
            console.log('ModelView');
            $('#generic_modal').remove()
            data = {};
            data.item = this.model.toJSON()
            templ = _.template this.template
            @$el.append(templ(data));
            $('#generic_modal').modal('show');
            return

    return {
        view: ModalView
    }

About = (apiPath) ->
    
    AboutModel = Backbone.Model.extend
        idAttribute: 'id'
        url: apiPath
        initialize: ->
        defaults: {} 
    
    AboutView = Backbone.View.extend
        el: '#block_container'
        template: TEMPLATES.about
        initialize: ->
            _this = this                       
            this.model.bind 'change', ->
                _this.render()                      
            return
        render: ->
            data = {};
            data.item = this.model.toJSON()
            templ = _.template this.template
            $newItems = $(templ(data))
            CONFIG.$cont.isotope('insert', $newItems)
            setTimeout ->
                CONFIG.$cont.isotope
                    sortBy: 'number'
                    sortAscending: false            
            ,1000
            return
    return {
        view: AboutView
        model: AboutModel 
    }

Block = (apiPath) ->
    
    BlockModel = Backbone.Model.extend
        idAttribute: 'id'
        initialize: ->
        defaults: {} 
    
    BlockCollection = Backbone.Collection.extend
        model: BlockModel
        url: apiPath
           
    BlockView = Backbone.View.extend
        el: '#block_container'
        template: TEMPLATES.block
        initialize: ->
            _this = this                       
            this.collection.bind 'reset', ->
                _this.render()                      
            return
        render: ->
            data = {};
            data.items = this.collection.toJSON()
            templ = _.template this.template
            $newItems = $(templ(data))
            CONFIG.$cont.isotope('insert', $newItems)
            setTimeout ->
                CONFIG.$cont.isotope
                    sortBy: 'number'
                    sortAscending: false            
            ,1000
            $('.item-link, .profile-link').tooltip()
            return
    return {
        view: BlockView
        model: BlockModel 
        collection: BlockCollection 
    }


init = ->
    CONFIG.$cont.isotope
        itemSelector : '.page-item',
        layoutMode : 'masonry'
        getSortData: 
            number: ($el) -> 
                parseInt($el.attr('data-epoch'), 10)


    
    $(document).on 'click', '.instagram-type .item-image img', (e) ->
        e.preventDefault()
        console.log 'hello2'
        id = $(e.target).parents('.instagram-type').attr 'data-id'
        item = Bootstrap.Collections.instagram.get id
        modal = new Modal()
        modalView = new modal.view 
            model: item
        return    

    $('.main-nav ul li').on 'click', 'a.top-level', (e) ->
        #filter
        e.preventDefault()
        filter = $(this).attr 'data-filter'
        CONFIG.$cont.isotope({ filter: filter });
        loc = $(this).parent().index()
        $('.main-nav ul li a').removeClass 'active'
        $(this).addClass 'active'
        $('.the-question').fadeOut 500
        $('.the-question').eq(loc).delay(500).fadeIn 500
        return
    
    $('.main-nav ul li').on 'click', 'ul.dropdown-menu li a', (e) ->
        #filter
        e.preventDefault()
        filter = $(this).attr 'data-filter'
        CONFIG.$cont.isotope({ filter: filter });
        loc = $(this).parent().index()
        $('.main-nav ul > li > a').removeClass 'active'
        $(this).parents('ul.dropdown-menu').siblings('a.top-level').addClass 'active'
        loc = $('.main-nav ul > li > a.active').index()
        $('.the-question').fadeOut 500
        $('.the-question').eq(loc).delay(500).fadeIn 500
        return
    
    $(document).on('mouseenter', '.page-item', (e) ->
        $(this).find('.icon-wrapper').fadeIn 250
        return
    ).on('mouseleave', '.page-item', (e) ->
        $(this).find('.icon-wrapper').fadeOut 250
        return
    )
    
    $('.email-type form').on 'click', 'button', (e) ->
        e.preventDefault()
        emailData = {}
        emailData.email = $('.email-type form input[name=email]').val()
        emailData.message = $('.email-type form textarea[name=message]').val()
        console.log 'button clicked'
        $.ajax
            url: config.baseUrl+'/api/send_email.php'
            data: emailData
            success: (data) ->
                console.log data
                if data.success
                    $('.email-type .contact-form').hide()
                    $('.email-type .contact-success').show()
                    return
                else
                    alert 'there was an issue with send your message'
                    return
        return

    ###
    tu = new Block CONFIG.baseUrl+'api/instagram.php'
    tuc = new tu.collection()
    tuv = new tu.view
        collection: tuc
    tuc.fetch()
    ###

    fb = new Block CONFIG.baseUrl+'api/facebook.php'
    fbc = new fb.collection()
    fbv = new fb.view
        collection: fbc
    fbc.fetch()
    Bootstrap.Collections.facebook = fbc

    i = new Block CONFIG.baseUrl+'api/instagram.php'
    ic = new i.collection()
    iv = new i.view
        collection: ic
    ic.fetch()
    Bootstrap.Collections.instagram = ic

    t = new Block CONFIG.baseUrl+'api/twitter.php'
    tc = new t.collection()
    tv = new t.view
        collection: tc
    tc.fetch()
    Bootstrap.Collections.twitter = tc

    f = new Block CONFIG.baseUrl+'api/foursquare.php'
    fc = new f.collection()
    fv = new f.view
        collection: fc
    fc.fetch()
    Bootstrap.Collections.foursquare = fc

    g = new Block CONFIG.baseUrl+'api/github.php'
    gc = new g.collection()
    gv = new g.view
        collection: gc
    gc.fetch()
    Bootstrap.Collections.github = gc

    p = new Block CONFIG.baseUrl+'api/projects.php'
    pc = new p.collection()
    pv = new p.view
        collection: pc
    pc.fetch()
    Bootstrap.Collections.projects = pc

    a = new About CONFIG.baseUrl+'api/linkedin.php'
    am = new a.model()
    av = new a.view
        model: am
    am.fetch()

init();

ga = ->
    events = ->
        $(document).on 'click', '.main-nav ul li a', ->
            _gaq.push ['_trackEvent', 'home_page', 'main_nav', $(this).attr 'data-filter']
    return {
        events: events
    }
g = new ga()
g.events()

window.Bootstrap = Bootstrap
window.About = About
window.Block = Block

