function manager(urlCfg, urlFilelist, deftCfg, deftFilelist, intFilelistFetch) {

    intFilelistFetch = (intFilelistFetch || 60)* 1000;

    var Cfg = Backbone.Model.extend({
        initialize: function(){
        },
        sync: function(method, model, options){
            var params = method == 'read'?
                {
                    type: 'GET', url: urlCfg, dataType: 'json'
                } :
                {
                    type: 'POST', url: urlCfg,
                    contentType: 'application/json',
                    data: JSON.stringify(options.attrs || model.toJSON(options))
                };
            var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
            model.trigger('request', model, xhr, options);
            return xhr;
        },
        buildUrlAttrs: function(url, i){
            var title = url.substr(url.indexOf('://')+3);
            if (title.length > 35) title = title.substr(0, 35)+ '...';
            return {id: url, title: title, i: typeof i != 'undefined'? i+1 : 0};
        },
        syncUrls: function(method, model, options){
            var resp, errMsg = 'Sync error';
            var dfd = Backbone.$ ?
              (Backbone.$.Deferred && Backbone.$.Deferred()) :
              (Backbone.Deferred && Backbone.Deferred());
            var urls = this.get('app_urls'), url;
            if (urls)
            switch (method) {
            case 'read':
                resp = _.map(urls, this.buildUrlAttrs);
                break;
            case 'create': case 'update':
                url = model.attributes.id;
                if (_.indexOf(urls, url) < 0)
                    this.save({app_urls: _.union(urls, url)});
                break;
            case 'delete':
                url = model.attributes.id;
                if (_.indexOf(urls, url) >= 0)
                    this.save({app_urls: _.without(urls, url)});
                break;
            default:
            }
            if (resp) {
                if (options && options.success) options.success(resp);
                if (dfd) dfd.resolve(resp);
            } else {
                if (options && options.error) options.error(errMsg);
                if (dfd) dfd.reject(errMsg);
            }
            if (options && options.complete) options.complete(resp);
            return dfd && dfd.promise();
        }
    });
    var cfg = new Cfg(deftCfg);

    var Url = Backbone.Model.extend({
        defaults: {
            id: '',
            title: '',
            i: 0
        },
        initialize: function() {
        },
        sync: function(method, model, options){
            return cfg.syncUrls(method, model, options);
        }
    });

    var UrlList = Backbone.Collection.extend({
        model: Url,
        initialize: function() {
            this.listenTo(cfg, 'sync', this.onCfgSync);
        },
        sync: function(method, list, options){
            return cfg.syncUrls(method, list, options);
        },
        onCfgSync: function(model, resp, options) {
            if (resp) {
                this.fetch();
            }
            else {
                cfg.fetch();
                callFilelistFetch();
            }
        }
    });
    var urlList = new UrlList();

    var File = Backbone.Model.extend({
        defaults: {
            id: '',
            path: '',
            url: '',
            time: 0,
            ip: '',
            useragent: '',
            display: true,
            content: '',
            i: 0
        },
        sync: function(method, model, options) {
            var params = {
                type: 'POST', url: urlFilelist,
                data: {id: model.attributes.id}
            };
            var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
            model.trigger('request', model, xhr, options);
            return xhr;
        },
        setDisplay: function(b) {
            this.set('display', b);
        }
    });
    var fileContent = new File({display:false});

    var FileList = Backbone.Collection.extend({
        model : File,
        initialize: function() {
            this.lastfetch = 0;
        },
        parse: function(response) {
            var aResp = [], i = 0;
            for (var key in response) {
                if (typeof key != 'string') continue;
                if (key == 'stamp') {
                    this.lastfetch = response.stamp; continue;
                }
                var arr = response[key];
                aResp.push({
                    id: key, path: cfg.get('dir')+key, url: arr[0],
                    time: (new Date(arr[1]*1000)).toLocaleString(),
                    ip: arr[2], useragent: arr[3], i: ++i
                });
            }
            return aResp;
        },
        sync: function(method, list, options) {
            if (method != 'read') return false;
            var params = {
                type: 'GET', url: urlFilelist,
                data: {stamp: this.lastfetch},
                dataType: 'json'
            };
            var xhr = options.xhr = Backbone.ajax(_.extend(params, options));
            list.trigger('request', list, xhr, options);
            return xhr;
        },
        filterByUrl: function(url) {
            this.each(function(model) {
                model.set('display', !url || model.get('url') == url);
            });
        },
        filterByIp: function(ip) {
            if (!ip) return;
            this.each(function(model) {
                if (model.get('display'))
                    model.set('display', model.get('ip') == ip);
            });
        },
        destroyVisible: function() {
            var list = this.where({'display': true});
            if (!list.length) return;
            var dfd = 0;
            _.each(list, function(model) {
                dfd = model.destroy();
            });
            if (dfd) dfd.done(callFilelistFetch);
        }
    });
    var fileList = new FileList();

    var fetchCount = 0, fetchId = 0, fetchFlag = true;
    function callFilelistFetch() {
        if (fetchId) clearTimeout(fetchId);
        if (fetchCount && fetchFlag) fileList.fetch({remove: true});
        fetchCount = 1;
        fetchId = setTimeout(callFilelistFetch, intFilelistFetch);
    }
    function setFilelistFetchFlag(b) {
        fetchFlag = Boolean(b);
    }

    var Selection = Backbone.Model.extend({
        defaults: {
            value: 0
        },
        initialize: function(idLs){
            this.on('change', this.apply);
            this.idLs = idLs && 'localStorage' in window && window.localStorage?
                idLs : 0;
            var v;
            if (this.idLs && (v = localStorage.getItem(this.idLs)))
                this.set('value', v);
            this.apply();
        },
        select: function(value, bUseDesel) {
            var b = this.get('value') != value;
            if (!b && !bUseDesel) return;
            this.set('value', b? value : 0);
            if (this.idLs) localStorage.setItem(this.idLs, this.get('value'));
        },
        apply: function() {
        }
    });

    var UrlSelection = Selection.extend({
        initialize: function() {
            Selection.prototype.initialize.call(this, 'sel-url');
        },
        getModel: function() {
            return this.get('value')?
                urlList.findWhere({'id': this.get('value')}) : false;
        },
        apply: function() {
            var v = this.get('value');
            fileList.filterByUrl(v? v : 0);
        }
    });
    var urlSelection = new UrlSelection();
    
    var FileSelection = Selection.extend({
        initialize: function() {
            Selection.prototype.initialize.call(this, 'sel-file');
        },
        getModel: function() {
            return this.get('value')?
                fileList.findWhere({id: this.get('value'), display: true})
                : false;
        }
    });
    var fileSelection = new FileSelection();
    
    var FileFilter = Backbone.Model.extend({
        defaults: {
            value: false
        },
        initialize: function(){
            this.listenTo(fileList, 'change:display', this.reset);
        },
        reset: function() {
            this.set('value', false);
            setFilelistFetchFlag(true);
        },
        setValue: function(v) {
            this.set('value', v);
            setFilelistFetchFlag(false);
        }
    });
    var fileFilter = new FileFilter();

    var UrlView = Backbone.View.extend({
        tagName: 'li',
        template: _.template( $('#url-template').html() ),
        events: {
            'click a.url-item' : 'select',
            'click a.remove' : 'destroy'
        },
        initialize: function() {
            this.listenTo(this.model, 'destroy', function() {
                this.remove();
            });
            this.listenTo(this.model, 'remove', function() {
                this.model.clear({silent:true});
                this.remove();
            });
        },
        render: function() {
            this.$el.html( this.template( this.model.toJSON() ) );
            return this;
        },
        select: function(e) {
            e.preventDefault();
            urlSelection.select(this.model.get('id'), true);
        },
        destroy: function(e) {
            e.preventDefault();
            this.model.destroy();
        }
    });

    var UrlListView = Backbone.View.extend({
        el: '#url-list',
        initialize: function() {
            this.listenTo(urlList, 'add', this.addOne);
            this.listenTo(urlList, 'all', this.render);
            urlList.fetch();
        },
        render: function() {
            if (urlList.length)
                this.$el.show();
            else
                this.$el.hide();
            return this;
        },
        addOne: function(item) {
            if (!('id' in item.attributes)) return;
            var urlView = new UrlView({model:item});
            this.$el.append(urlView.render().el);
        },
        addAll: function() {
            urlList.each(this.addOne, this);
        }
    });
    var urlListView = new UrlListView();

    var FileView = Backbone.View.extend({
        tagName: 'li',
        template: _.template( $('#file-template').html() ),
        events: {
            'click a.file-item' : 'select',
            'click a.remove' : 'destroy'
        },
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(this.model, 'destroy', function() {
                this.remove();
            });
            this.listenTo(this.model, 'remove', function() {
                this.model.clear({silent:true});
                this.remove();
            });
        },
        render: function() {
            if (this.model.get('display'))
                this.$el.html( this.template( this.model.toJSON() ) ).show();
            else
                this.$el.hide();
            return this;
        },
        select: function(e) {
            e.preventDefault();
            fileSelection.select(this.model.get('id'));
        },
        destroy: function(e) {
            e.preventDefault();
            this.model.destroy().done(callFilelistFetch);
        }
    });

    var FileListView = Backbone.View.extend({
        el: '#file-list',
        initialize: function() {
            this.listenTo(fileList, 'add', this.addOne);
            this.listenTo(fileList, 'reset', this.addAll);
            this.listenTo(fileList, 'all', this.render);
            fileList.reset(fileList.parse(deftFilelist));
            callFilelistFetch();
        },
        render: function() {
            if (fileList.length)
                this.$el.show();
            else
                this.$el.hide();
            return this;
        },
        addOne: function(item) {
            if (!('id' in item.attributes)) return;
            var fileView = new FileView({model:item});
            this.$el.append(fileView.render().el);
        },
        addAll: function() {
            fileList.each(this.addOne, this);
        }
    });
    var fileListView = new FileListView();

    var SelectionView = Backbone.View.extend({
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            if (this.model.get('value')) this.render();
        },
        render: function() {
            var els = this.$el.children('li');
            var i = this.getIndex();
            if (els.length) {
                els.removeClass('active');
                if (i && i <= els.length) els.eq(i-1).addClass('active');
            }
            return this;
        },
        getIndex: function() {
            return 0;
        }
    });

    var UrlSelectionView = SelectionView.extend({
        el: $('#url-list'),
        getIndex: function() {
            var model = this.model.getModel();
            return model? model.get('i') : 0;
        }
    });
    var urlSelectionView = new UrlSelectionView({model:urlSelection});

    var FileSelectionView = SelectionView.extend({
        el: $('#file-list'),
        initialize: function() {
            this.listenTo(fileList, 'change', this.render);
            this.listenTo(fileList, 'remove', this.render);
            this.cache = 0;
            SelectionView.prototype.initialize.call(this);
        },
        getIndex: function() {
            var model = this.model.getModel();
            this.callContent(this.model.get('value'), model);
            return model? model.get('i') : 0;
        },
        callContent: function(value, model) {
            if (!model) {
                fileContent.set('display', false);
                return;
            }
            if (value == this.cache) {
                fileContent.set('display', true);
                return;
            }
            var urlCont = model.get('path');
            fileContent.set({display: false, content: ''});
            $.ajax({
                url: urlCont, type: 'GET', dataType: 'text', context: this,
                success: function(text) {
                    this.cache = value;
                    fileContent.set(model.attributes);
                    fileContent.set('content', this.fixContent(text));
                }
            });
        },
        fixContent: function (text) {
            var map = {
              '&': '&amp;',
              '<': '&lt;',
              '>': '&gt;',
              '"': '&quot;',
              "'": '&#039;'
            };
            var i = text.indexOf('\n\n');
            if (i) text = text.substr(i);
            return $.trim(
                text.replace(/[&<>"']/g, function(m) { return map[m]; })
                .replace(/\n(\d+)\t/g, function(m, s) {
                    return '\n<strong>+'+s/1000+' sec:</strong>\n'; }
                )
            );
        }
    });
    var fileSelectionView = new FileSelectionView({model:fileSelection});

    var FileContentView = Backbone.View.extend({
        el: $('#file-content'),
        template: _.template( $('#content-template').html() ),
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },
        render: function() {
            if (this.model.get('display') && this.model.get('content')) {
                this.$el.html( this.template( this.model.toJSON() ) ).show();
            }
            else
                this.$el.hide();
            return this;
        }
    });
    var fileContentView = new FileContentView({model:fileContent});

    var CfgView = Backbone.View.extend({
        el: $('#config'),
        template: _.template( $('#config-template').html() ),
        events: {
            'click button.save' : 'saveForm'
        },
        initialize: function() {
            var self = this;
            this.$el.on('show.bs.modal', function () {
                self.render();
            });
        },
        render: function() {
            this.$('form').html( this.template( this.model.toJSON() ) );
            return this;
        },
        saveForm: function(e) {
            e.preventDefault();
            var attrs = {};
            _.each(this.$('form').serializeArray(), function(obj) {
                attrs[obj.name] = parseFloat(obj.value);
            });
            if (Object.keys(attrs).length) this.model.save(attrs, {wait: true});
            this.$el.modal('hide');
        }
    });
    var cfgView = new CfgView({model:cfg});

    var ControlsView = Backbone.View.extend({
        el: $('#controls'),
        events: {
            'keypress input' : 'create',
            'click button.filter' : 'toggle',
            'click button.refresh' : 'refresh',
            'click button.remove' : 'destroy'
        },
        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            this.btn = this.$('button.filter');
            this.$('input').val('');
        },
        render: function() {
            var v = this.model.get('value');
            if (v)
                this.btn.addClass('active').children('.value').html(': '+v);
            else
                this.btn.removeClass('active').children('.value').html('');
            return this;
        },
        create: function(e) {
            if (e.which != 13) return;
            var el = e.target;
            var v = $(el).val();
            if (!v) return;
            $(el).val('').blur();
            urlList.create(cfg.buildUrlAttrs(v));
        },
        refresh: function(e) {
            e.preventDefault();
            $(e.target).blur();
            callFilelistFetch();
        },
        toggle: function(e) {
            e.preventDefault();
            $(e.target).blur();
            var v = this.model.get('value');
            var model = v? false : fileSelection.getModel();
            if (!v && !model) return;
            if (!v) {
                v = model.get('ip');
                fileList.filterByIp(v);
                this.model.setValue(v);
            }
            else {
                this.model.reset();
                urlSelection.apply();
            }
        },
        destroy: function(e) {
            e.preventDefault();
            $(e.target).blur();
            fileList.destroyVisible();
        }
    });
    var controlsView = new ControlsView({model:fileFilter});
}
