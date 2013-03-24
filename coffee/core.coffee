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
            _this = @
            $('#generic_modal').remove()
            data = {}
            data.item = @model.toJSON()
            
            templ = _.template @template
            @$el.append templ data

            $('#generic_modal').on 'hidden', ->
                $('#generic_modal').remove()
                return

            $('#generic_modal').on 'shown', ->
                if data.item.type is 'foursquare'
                    _this.displayMap data
                else if data.item.type is 'vine'
                    _this.displayVideo data
                return
            
            $('#generic_modal').modal 'show'
            
            return
        
        displayMap: (data)->
            _this = @;
            myLatLng = new google.maps.LatLng data.item.stash.lat, data.item.stash.lng
            mapOptions =
                mapTypeControl: true
                mapTypeControlOptions: 
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                zoomControl: true
                zoomControlOptions:
                    style: google.maps.ZoomControlStyle.SMALL
                center: myLatLng
                zoom: 12
                mapTypeId: google.maps.MapTypeId.ROADMAP
        
            @map = new google.maps.Map document.getElementById("map_container"), mapOptions
            
            infowindow = new google.maps.InfoWindow
                content: data.item.title
                   
            @marker = new google.maps.Marker
                position: myLatLng
                map: @map
                title: data.item.title
            
            google.maps.event.addListener @marker, 'click', ->
                console.log 'clicked marker'
                infowindow.open _this.map, _this.marker
                                 
            return
        displayVideo: (data)->
            return 'hello' 
    return {
        View: ModalView
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
            _this = @                    
            @collection.bind 'reset', ->
                _this.render()                      
            return
        render: ->
            data = {};
            data.items = @collection.toJSON()
            templ = _.template @template
            $newItems = $(templ(data))
            CONFIG.$cont.isotope('insert', $newItems)
            setTimeout ->
                CONFIG.$cont.isotope
                    sortBy: 'number'
                    sortAscending: false            
                filter = $('.main-nav a.active').attr 'data-filter'
                CONFIG.$cont.isotope filter: filter
                return
            , 1000
            $('.item-link, .profile-link').tooltip()
            getVineVideos @collection            
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
        id = $(e.target).parents('.instagram-type').attr 'data-id'
        item = Bootstrap.Collections.instagram.get id
        modal = new Modal()
        modalView = new modal.View
            model: item
        return    

    $(document).on 'click', '.foursquare-type .item-image img', (e) ->
        e.preventDefault()
        id = $(e.target).parents('.foursquare-type').attr 'data-id'
        item = Bootstrap.Collections.foursquare.get id
        console.log item.toJSON()
        modal = new Modal()
        modalView = new modal.View
            model: item
        return    
    
    $(document).on 'click', '.vine-type .item-image img', (e) ->
        e.preventDefault()
        id = $(e.target).parents('.vine-type').attr 'data-id'
        item = Bootstrap.Collections.twitter.get id
        console.log item.toJSON()
        modal = new Modal()
        modalView = new modal.View
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
            url: CONFIG.baseUrl+'api/send_email.php'
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


ga = ->
    events = ->
        $(document).on 'click', '.main-nav ul li a', ->
            _gaq.push ['_trackEvent', 'home_page', 'main_nav', $(this).attr 'data-filter']
    return {
        events: events
    }

getVineVideos = (c) ->
    vineModels = c.where 'type': 'vine'
    if vineModels.length > 0
        for model in vineModels
            $.ajax
                url: '/api/get_vine_video.php'
                data: 
                    id: model.id
                    vine_url: model.get 'link' 
                success: (data) ->
                    $el = $('[data-id='+data.id+']')
                    vineModel = c.get data.id
                    vineModel.set data
                    $el.find('.item-image img').attr('src', data.screen_url);
                    return
    return


init()
g = new ga()
g.events()

window.Bootstrap = Bootstrap
window.About = About
window.Block = Block

