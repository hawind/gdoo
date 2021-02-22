/**
 * Gdoo Realtime 连接库
 *
 * 参考自：https://github.com/joewalnes/reconnecting-websocket/
 *
 * 使用方法
 * ======
 * 选项可以在实例化时传递，也可以在实例化后设置：
 * var socket = new Realtime({url: '127.0.0.1', debug: true, reconnectInterval: 4000});
 * 或者
 * var socket = new Realtime();
 * socket.url = '';
 * socket.debug = true;
 * socket.reconnectInterval = 4000;
 */
(function (global, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module !== 'undefined' && module.exports){
        module.exports = factory();
    } else {
        global.Realtime = factory();
    }
})(this, function () {

    if (!('WebSocket' in window)) {
        return;
    }

    function Realtime(appKey, options) {
        // 默认设置
        var settings = {
            // 连接ws服务器地址
            host: null,
            // 连接服务器认证的url
            authEndpoint: '/realtime/auth',
            // 认证参数
            auth: {params:{}, headers:{}},
            // 此实例是否应记录调试消息
            debug: false,
            // WebSocket是否应在实例化后立即尝试连接
            automaticOpen: true,
            // 尝试重新连接之前要延迟的毫秒数
            reconnectInterval: 1000,
            // 延迟重新连接尝试的最大毫秒数
            maxReconnectInterval: 30000,
            // The rate of increase of the reconnect delay. Allows reconnect attempts to back off when problems persist. 
            reconnectDecay: 1.5,
            // The maximum time in milliseconds to wait for a connection to succeed before closing and retrying. 
            timeoutInterval: 2000,
            // The maximum number of reconnection attempts to make. Unlimited if null. */
            maxReconnectAttempts: null,
            // The binary type, possible values 'blob' or 'arraybuffer', default 'blob'. */
            binaryType: 'blob',
            // websocket子协议定义
            protocols: [],
            // 当前连接Id
            socket_id: null,
        }
        if (!options) { options = {}; }

        // Overwrite and define settings with options if they exist.
        for (var key in settings) {
            if (typeof options[key] == 'undefined') {
                this[key] = settings[key];
            } else {
                this[key] = options[key];
            }
        }

        /** The number of attempted reconnects since starting, or the last successful connection. Read only. */
        this.reconnectAttempts = 0;

        /**
         * The current state of the connection.
         * Can be one of: WebSocket.CONNECTING, WebSocket.OPEN, WebSocket.CLOSING, WebSocket.CLOSED
         * Read only.
         */
        this.readyState = WebSocket.CONNECTING;

        /**
         * A string indicating the name of the sub-protocol the server selected; this will be one of
         * the strings specified in the protocols parameter when creating the WebSocket object.
         * Read only.
         */
        this.protocol = null;

        // 私有变量
        var me = this;
        var ws;
        var forcedClose = false;
        var timedOut = false;

        var handlers = {};

        handlers.connect = function(data) {};
        handlers.disconnect = function(data) {};
        handlers.reconnecting = function(data) {};
        handlers.quit = function(data) {};
        handlers.error = function(data) {};

        this.on = function(name, fun) {
            handlers[name] = fun;
            return me;
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

        this.subscribe = function(channel_name) {
            return new channel(channel_name);
        }

        this.authorize = function() {
            $.ajax({
                url: me.authEndpoint,
                type: 'POST',
                data: me.auth['params'],
                beforeSend: function(req) {
                    headers = me.auth['headers'];
                    for (var key in headers) {
                        req.setRequestHeader(key, headers[key]);
                    }
                },
                dataType: 'json',
                success: function (data) {
                    me.send({channel:'socket', event:'authorize', data:data});
                }
            });
        }

        this.connect = function (reconnectAttempt) {
            ws = new WebSocket(me.host + '?key=' + appKey, me.protocols);
            ws.binaryType = this.binaryType;

            if (reconnectAttempt) {
                if (this.maxReconnectAttempts && this.reconnectAttempts > this.maxReconnectAttempts) {
                    return;
                }
            } else {
                handlers.reconnecting.call(me);
                this.reconnectAttempts = 0;
            }

            if (me.debug || Realtime.debugAll) {
                console.log('realtime', 'attempt-connect', me.host);
            }

            var localWs = ws;
            var timeout = setTimeout(function() {
                if (me.debug || Realtime.debugAll) {
                    console.log('realtime', 'connection-timeout', me.host);
                }
                timedOut = true;
                localWs.close();
                timedOut = false;
            }, me.timeoutInterval);

            ws.onopen = function(event) {
                clearTimeout(timeout);
                if (me.debug || Realtime.debugAll) {
                    console.log('realtime', 'onopen', me.host);
                }
                me.protocol = ws.protocol;
                me.readyState = WebSocket.OPEN;
                me.reconnectAttempts = 0;

                handlers.connect.call(me);

                reconnectAttempt = false;
            };

            ws.onclose = function(event) {
                clearTimeout(timeout);
                ws = null;
                if (forcedClose) {
                    me.readyState = WebSocket.CLOSED;
                    handlers.disconnect.call(me, event);
                } else {
                    me.readyState = WebSocket.CONNECTING;
                    handlers.reconnecting.call(me, event);

                    if (!reconnectAttempt && !timedOut) {
                        if (me.debug || Realtime.debugAll) {
                            console.log('realtime', 'onclose', me.host);
                        }
                        handlers.disconnect.call(me, event);
                    }

                    var timeout = me.reconnectInterval * Math.pow(me.reconnectDecay, me.reconnectAttempts);
                    setTimeout(function() {
                        me.reconnectAttempts++;
                        me.connect(true);
                    }, timeout > me.maxReconnectInterval ? me.maxReconnectInterval : timeout);
                }
            };

            ws.onmessage = function(event) {
                if (me.debug || Realtime.debugAll) {
                    console.log('realtime', 'onmessage', me.host, event.data);
                }
                var packet = JSON.parse(event.data);
                
                var channel_name = packet['channel'];
                var event_name   = packet['event'];
                var data         = packet['data'];
                var key          = channel_name == 'socket' ? event_name : channel_name + '.' + event_name;
                var handler      = handlers[key];

                // 连接成功事件
                if (key == 'connected') {
                    me.socket_Id = data.socket_Id;
                    me.authorize();
                }
                if (typeof handler == 'function') {
                    handler.call(me, data, packet);
                }
            };

            ws.onerror = function(event) {
                if (me.debug || Realtime.debugAll) {
                    console.log('realtime', 'onerror', me.host, event);
                }
                handlers.error.call(me, event);
            };
        }

        // 是否在实例化时创建WebSocket
        if (this.automaticOpen == true) {
            this.connect(false);
        }

        /**
         * 通过WebSocket连接将数据传输到服务器。
         * @param channel 频道名称
         * @param data    要发送到服务器的文本字符串、arrayBuffer或blob。
         */
        this.emit = function(channel_name, event_name, data) {
            return this.send({channel:channel_name, event:event_name, data:data});
        };

        this.send = function(data) {
            if (ws) {
                if (me.debug || Realtime.debugAll) {
                    console.log('realtime', 'send', me.host, data.data);
                }
                return ws.send(JSON.stringify(data));
            } else {
                throw 'INVALID_STATE_ERR : Pausing to reconnect websocket';
            }
        };

        /**
         * 关闭WebSocket连接或连接尝试。
         * 如果连接已关闭，则此方法不执行任何操作。
         */
        this.disconnect = function(code, reason) {
            // Default CLOSE_NORMAL code
            if (typeof code == 'undefined') {
                code = 1000;
            }
            forcedClose = true;
            if (ws) {
                ws.close(code, reason);
            }
        };
    }

    /**
     * 将此设置为true等同于将 Realtime.log 的所有实例设置为true。
     */
    Realtime.debugAll = false;

    Realtime.CONNECTING = WebSocket.CONNECTING;
    Realtime.OPEN = WebSocket.OPEN;
    Realtime.CLOSING = WebSocket.CLOSING;
    Realtime.CLOSED = WebSocket.CLOSED;

    return Realtime;
});
