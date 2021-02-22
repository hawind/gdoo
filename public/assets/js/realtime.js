function createRealtime() {
    var me = this;
    var lockReconnect = false;
    var reconnectTimeout = null;
    var ws;
    var handlers = {};
    handlers.open = function(data) {};
    handlers.message = function(data) {};
    handlers.close = function(data) {};
    handlers.pong = function(data) {};
    handlers.error = function(data) {};

    var socketUrl = null;
    this.on = function(name, fun) {
        handlers[name] = fun;
        return this;
    }
    this.connect = function(url) {
        socketUrl = url;
        createWebSocket();
    }

    /*
    // 注册频道，暂时没用
    this.subscribe = function(channel_name) {
        return new channel(channel_name);
    }

    var channel = function(channel_name) {
        var that = this;
        this.channel_name = channel_name;
        this.members = {};
        this.on = function(event_name, callback) {
            handlers[that.channel_name + '.' + event_name] = callback;
            return that;
        }
        this.emit = function(event_name, data) {
            return me.emit(that.channel_name, event_name, data);
        }
    }

    this.emit = function(channel_name, event_name, data) {
        return ws.send({channel:channel_name, event:event_name, data:data});
    };
    */

    function createWebSocket() {
        try {
            ws = new WebSocket(socketUrl);
            me.ws = ws;
            init();
        } catch(e) {
            console.debug('WebSocket catch', e);
            reconnect();
        }
    }

    function init() {
        ws.onclose = function (event) {
            handlers.close.call(me, event);
            reconnect();
        };
        ws.onerror = function(event) {
            handlers.error.call(me, event);
            reconnect();
        };
        ws.onopen = function (event) {
            handlers.open.call(me, event);
            heartCheck.reset();
        };
        ws.onmessage = function (res) {
            var packet = JSON.parse(res.data);
            var event = packet['event'];
            var data = packet['data'];
            var handler = handlers[event];
            if (typeof handler == 'function') {
                handler.call(me, data, packet);
            } else {
                handlers.message.call(me, data, packet);
            }
            heartCheck.reset();
        }
    }

    function reconnect() {
        if (lockReconnect) {
            return;
        };
        lockReconnect = true;
        if (reconnectTimeout) {
            clearTimeout(reconnectTimeout);
        }
        reconnectTimeout = setTimeout(function () {
            createWebSocket();
            lockReconnect = false;
        }, 5000);
    }

    var heartCheck = {
        interval: 1000 * 5,
        timeout: null,
        serverTimeout: null,
        reset: function () {
            clearTimeout(this.timeout);
            clearTimeout(this.serverTimeout);
            this.start();
        },
        start: function () {
            heartCheck.timeout = setTimeout(function() {
                console.debug("ping...");
                ws.send('{"event":"ping"}');
                heartCheck.serverTimeout = setTimeout(function() {
                    ws.close(); 
                }, heartCheck.interval);
            }, heartCheck.interval);
        }
    }
    return this;
}