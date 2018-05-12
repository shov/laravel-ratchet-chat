<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
    <script
            src="https://code.jquery.com/jquery-3.3.1.min.js"
            integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
            crossorigin="anonymous"></script>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">Home</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    @endif

    <div class="content">
        <div class="title m-b-md">
            Laravel
        </div>

        <div class="links">
            <a href="https://laravel.com/docs">Documentation</a>
            <a href="https://laracasts.com">Laracasts</a>
            <a href="https://laravel-news.com">News</a>
            <a href="https://forge.laravel.com">Forge</a>
            <a href="https://github.com/laravel/laravel">GitHub</a>
        </div>
        <div id="chat-container">
            <pre id="chat-body"></pre>
            <input id="chat-field" type="text">
        </div>
        <style>
            #chat-body {
                width: 100%;
                text-align: left;
            }

            #chat-field {
                width: 100%;
                background-color: #ddd;
            }
        </style>
        <script>
            (function ($) {

                var ChatBody = function (container) {
                    var domEl = $('#chat-body');
                    if(domEl.length < 1) {
                        throw "No chat body!";
                    }

                    var lines = [];

                    this.printMe = function(msg)
                    {
                        lines.push('[me]: ' + msg);
                        renew();
                    };

                    this.print = function(msg)
                    {
                        lines.push(msg);
                        renew();
                    };

                    this.systemMessage = function(msg)
                    {
                        lines.push('[#]: ' + msg);
                        renew();
                    };

                    function renew() {
                        var text = lines.join("\n");
                        domEl.text(text);
                    }
                };

                var ChatField = function (container) {
                    var domEl = $('#chat-field');
                    if(domEl.length < 1) {
                        throw "No chat field!";
                    }

                    domEl.on('keyup', function (e) {
                        if(13 === e.keyCode) {
                            sendMessage();
                        }
                    });

                    function sendMessage()
                    {
                        container
                            .chatBody
                            .printMe(
                                domEl.val()
                            );

                        container
                            .webSocket
                            .send(domEl.val());

                        domEl.val('');
                    }
                };

                var ChatWebSocket = function (container) {
                    var conn = new WebSocket('ws://{{ Request::getHttpHost() }}:8080');

                    conn.onopen = function (e) {
                        container.chatBody.systemMessage("Connected to chat server");
                    };

                    conn.onmessage = function (e) {
                        container.chatBody.print(e.data);
                    };

                    conn.onclose = function (e) {
                        container.chatBody.systemMessage("Connection lost");
                    };

                    conn.onerror = function (e) {
                        container.chatBody.systemMessage("Error: " + e);
                    };

                    this.send = function (msg) {
                      conn.send(msg);
                    };
                };

                window.Chat = new function () {
                    try {
                        this.chatBody = new ChatBody(this);
                        this.webSocket = new ChatWebSocket(this);
                        this.chatField = new ChatField(this);
                    } catch (e) {
                        console.log(e);
                    }

                    this.chatBody.systemMessage("Ready to chat!");
                };

            })(jQuery);
        </script>
    </div>
</div>
</body>
</html>
